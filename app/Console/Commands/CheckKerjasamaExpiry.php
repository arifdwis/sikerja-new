<?php

namespace App\Console\Commands;

use App\Models\Permohonan;
use App\Models\KerjasamaManual;
use App\Models\User;
use App\Models\Notifikasi;
use App\Services\WhatsappService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckKerjasamaExpiry extends Command
{
    protected $signature = 'kerjasama:check-expiry';
    protected $description = 'Cek kerjasama yang akan/sudah berakhir, kirim reminder bertingkat ke OPD, Pemohon, dan Admin';

    /**
     * Reminder bertingkat (Req 14):
     *  - 90 hari: Reminder 3 bulan sebelum berakhir
     *  - 60 hari: Reminder 2 bulan sebelum berakhir
     *  - 30 hari: Reminder 1 bulan sebelum berakhir
     *  - 7 hari : Reminder 1 minggu sebelum berakhir
     *  - 0 hari : Hari terakhir kerjasama berakhir, sudah saatnya monev
     *
     * Toleransi ± 1 hari agar fleksibel saat scheduler agak telat jalan.
     */
    protected array $reminderWindows = [
        ['days' => 90, 'label' => '3 bulan',  'level' => 'info'],
        ['days' => 60, 'label' => '2 bulan',  'level' => 'info'],
        ['days' => 30, 'label' => '1 bulan',  'level' => 'warning'],
        ['days' => 7,  'label' => '1 minggu', 'level' => 'warning'],
        ['days' => 0,  'label' => 'hari ini', 'level' => 'critical'],
    ];

    public function handle(WhatsappService $whatsapp): int
    {
        $this->info('Mengecek kerjasama yang akan/sudah berakhir...');

        // 1) Auto-transition status Pelaksanaan → Selesai untuk kerjasama yang sudah lewat tanggal_berakhir.
        //    Selesai = tanggal kerjasama habis, BUKAN tergantung monev.
        $this->autoCloseExpiredPermohonan();

        $totalSent = 0;
        foreach ($this->reminderWindows as $window) {
            $totalSent += $this->processWindow($whatsapp, $window);
        }

        $this->info("Selesai. Total reminder dikirim: {$totalSent}");
        return self::SUCCESS;
    }

    /**
     * Flip status Pelaksanaan (6) → Selesai (7) untuk semua permohonan
     * yang tanggal_berakhir-nya sudah <= hari ini.
     */
    protected function autoCloseExpiredPermohonan(): void
    {
        $expired = Permohonan::where('status', Permohonan::STATUS_PELAKSANAAN)
            ->whereNotNull('tanggal_berakhir')
            ->where('tanggal_berakhir', '<=', now())
            ->get();

        if ($expired->isEmpty()) {
            return;
        }

        $count = 0;
        foreach ($expired as $p) {
            $p->update([
                'status' => Permohonan::STATUS_SELESAI,
            ]);

            // Catat di histori (kalau model histori tersedia)
            try {
                \App\Models\PermohonanHistori::create([
                    'id_permohonan' => $p->id,
                    'id_operator' => null,
                    'deskripsi' => 'Status otomatis berubah ke Selesai karena tanggal berakhir kerjasama telah terlampaui.',
                ]);
            } catch (\Throwable $e) {
                // diam-diam — observer/relasi histori opsional
            }

            $count++;
        }

        $this->info("Auto-transition Pelaksanaan → Selesai: {$count} permohonan ditandai Selesai.");
    }

    protected function processWindow(WhatsappService $whatsapp, array $window): int
    {
        $days = $window['days'];
        $start = now()->addDays($days - 1)->startOfDay();
        $end = now()->addDays($days + 1)->endOfDay();

        $sent = 0;
        $sent += $this->processPermohonan($whatsapp, $start, $end, $window);
        $sent += $this->processKerjasamaManual($whatsapp, $start, $end, $window);
        return $sent;
    }

    protected function processPermohonan(WhatsappService $whatsapp, Carbon $start, Carbon $end, array $window): int
    {
        $permohonans = Permohonan::with(['opds', 'operator', 'pemohon1'])
            ->whereIn('status', [Permohonan::STATUS_PELAKSANAAN, Permohonan::STATUS_SELESAI])
            ->whereNotNull('tanggal_berakhir')
            ->whereBetween('tanggal_berakhir', [$start, $end])
            ->get();

        $sent = 0;
        foreach ($permohonans as $p) {
            $this->sendReminderForPermohonan($whatsapp, $p, $window);
            $sent++;
        }
        return $sent;
    }

    protected function processKerjasamaManual(WhatsappService $whatsapp, Carbon $start, Carbon $end, array $window): int
    {
        $kms = KerjasamaManual::with(['opds', 'creator'])
            ->whereNotNull('tanggal_berakhir')
            ->whereBetween('tanggal_berakhir', [$start, $end])
            ->get();

        $sent = 0;
        foreach ($kms as $km) {
            $this->sendReminderForKerjasamaManual($whatsapp, $km, $window);
            $sent++;
        }
        return $sent;
    }

    protected function sendReminderForPermohonan(WhatsappService $whatsapp, Permohonan $p, array $window): void
    {
        $tglFormat = Carbon::parse($p->tanggal_berakhir)->translatedFormat('d F Y');
        $sisaHari = $window['days'] === 0 ? 0 : $window['days'];

        // Title singkat untuk inbox sistem
        if ($window['days'] == 0) {
            $title = 'Kerja Sama Berakhir Hari Ini';
            $shortMsg = "Kerja sama \"{$p->label}\" dengan {$p->nama_instansi} berakhir hari ini ({$tglFormat}). Mohon segera lakukan Monitoring & Evaluasi.";
        } else {
            $title = "Kerja Sama Akan Berakhir dalam {$window['label']}";
            $shortMsg = "Kerja sama \"{$p->label}\" dengan {$p->nama_instansi} akan berakhir pada {$tglFormat} ({$window['label']} lagi). Mohon dipersiapkan tindak lanjutnya.";
        }

        // 1. Pemohon (operator yang ajukan) — pakai template formal
        if ($p->id_pemohon_0) {
            $pemohonUser = User::find($p->id_pemohon_0);
            $namaPemohon = $p->pemohon1?->name ?: ($pemohonUser?->name ?: 'Pemohon');

            $payloadPemohon = \App\Services\NotificationTemplate::reminderMonevPemohon($namaPemohon, $p, $sisaHari);
            $this->createNotif($p->id_pemohon_0, $p->id, $payloadPemohon['system']['title'], $payloadPemohon['system']['message']);
            if ($pemohonUser?->phone) {
                try { $whatsapp->sendMessage($pemohonUser->phone, $payloadPemohon['wa']); } catch (\Throwable $e) { Log::error('Reminder WA pemohon: ' . $e->getMessage()); }
            }
        }

        // 2. TKKSD Lokus (OPD terkait) — pakai template formal OPD
        $opdIds = $p->opds()->pluck('opd.id')->toArray();
        if (!empty($opdIds)) {
            $opdUsers = User::whereIn('id_opd', $opdIds)
                ->whereHas('roles', fn($q) => $q->where('slug', 'tkksd_lokus'))
                ->with('opd')
                ->get();
            foreach ($opdUsers as $u) {
                $namaOpd = $u->opd?->nama ?? $u->name;
                $waOpd = \App\Services\NotificationTemplate::formalToOpd(
                    $title,
                    $namaOpd,
                    $p,
                    $sisaHari < 0 || $sisaHari === 0
                        ? "Kerja sama yang melibatkan instansi Bpk/Ibu telah/akan berakhir pada {$tglFormat}. Mohon mendampingi pemohon untuk pelaksanaan Monitoring & Evaluasi."
                        : "Kerja sama yang melibatkan instansi Bpk/Ibu akan berakhir pada {$tglFormat} ({$window['label']} lagi). Mohon Bpk/Ibu mendampingi proses Monev kerja sama tersebut."
                );
                $this->createNotif($u->id, $p->id, $title, $shortMsg);
                if ($u->phone) {
                    try { $whatsapp->sendMessage($u->phone, $waOpd); } catch (\Throwable $e) { Log::error('Reminder WA OPD: ' . $e->getMessage()); }
                }
            }
        }

        // 3. Admin (system + WA admin) — pakai whitelist via scope
        $admins = User::adminNotificationRecipients()->get();
        foreach ($admins as $a) {
            $this->createNotif($a->id, $p->id, $title, $shortMsg);
        }

        // 4. WA Grup admin — template internal formal
        $waGroup = \App\Services\NotificationTemplate::reminderMonevAdmin($p, $sisaHari);
        try { $whatsapp->sendToGroup($waGroup); } catch (\Throwable $e) { Log::error('Reminder WA group: ' . $e->getMessage()); }
    }

    protected function sendReminderForKerjasamaManual(WhatsappService $whatsapp, KerjasamaManual $km, array $window): void
    {
        $tglFormat = Carbon::parse($km->tanggal_berakhir)->translatedFormat('d F Y');

        if ($window['days'] == 0) {
            $title = 'Kerja Sama (Sistem Lama) Berakhir Hari Ini';
            $message = "Kerja sama \"{$km->label}\" dengan {$km->nama_instansi} berakhir hari ini ({$tglFormat}). Mohon admin meninjau tindak lanjutnya.";
        } else {
            $title = "Kerja Sama (Sistem Lama) Akan Berakhir dalam {$window['label']}";
            $message = "Kerja sama \"{$km->label}\" dengan {$km->nama_instansi} akan berakhir pada {$tglFormat} ({$window['label']} lagi). Mohon admin meninjau tindak lanjutnya.";
        }

        // Build pesan formal grup pakai template
        // KerjasamaManual bukan instance Permohonan — kita map ke Permohonan-like object cukup buat builder.
        $proxyPermohonan = new Permohonan([
            'label'              => $km->label,
            'nomor_permohonan'   => $km->nomor_pks,
            'nama_instansi'      => $km->nama_instansi,
        ]);

        $waGroup = \App\Services\NotificationTemplate::internalGroup(
            $title,
            $proxyPermohonan,
            $message
        );

        // OPD terkait
        $opdIds = $km->opds()->pluck('opd.id')->toArray();
        if (!empty($opdIds)) {
            $opdUsers = User::whereIn('id_opd', $opdIds)->with('opd')->get();
            foreach ($opdUsers as $u) {
                $namaOpd = $u->opd?->nama ?? $u->name;
                $waOpd = \App\Services\NotificationTemplate::formalToOpd($title, $namaOpd, $proxyPermohonan, $message);
                $this->createNotif($u->id, null, $title, $message);
                if ($u->phone) {
                    try { $whatsapp->sendMessage($u->phone, $waOpd); } catch (\Throwable $e) { Log::error('Reminder WA OPD manual: ' . $e->getMessage()); }
                }
            }
        }

        // Admin — pakai whitelist via scope
        $admins = User::adminNotificationRecipients()->get();
        foreach ($admins as $a) {
            $this->createNotif($a->id, null, $title, $message);
        }

        try { $whatsapp->sendToGroup($waGroup); } catch (\Throwable $e) { Log::error('Reminder WA group manual: ' . $e->getMessage()); }
    }

    protected function createNotif($userId, $permohonanId, $title, $message): void
    {
        try {
            // Anti-duplikat: jangan kirim notif yang sama untuk pengguna+permohonan dalam 24 jam terakhir
            $alreadyNotified = Notifikasi::where('id_user', $userId)
                ->where('id_permohonan', $permohonanId)
                ->where('title', $title)
                ->where('created_at', '>=', now()->subDay())
                ->exists();

            if ($alreadyNotified) {
                return;
            }

            Notifikasi::create([
                'id_user' => $userId,
                'id_permohonan' => $permohonanId,
                'type' => 'reminder',
                'title' => $title,
                'message' => $message,
                'is_read' => false,
            ]);
        } catch (\Exception $e) {
            Log::error('Reminder notif error: ' . $e->getMessage());
        }
    }

    protected function sendWa(WhatsappService $whatsapp, ?string $phone, string $title, string $message): void
    {
        if (!$phone) return;
        try {
            $whatsapp->sendMessage($phone, "*{$title}*\n\n{$message}\n\n_Sistem Kerjasama Daerah Samarinda_");
        } catch (\Exception $e) {
            Log::error('Reminder WA error: ' . $e->getMessage());
        }
    }

    /**
     * WA ke admin di-skip kalau notify_admin_enabled=false (development).
     */
    protected function sendWaToAdmin(WhatsappService $whatsapp, ?string $phone, string $title, string $message): void
    {
        if (!$phone) return;
        try {
            $whatsapp->sendToAdmin($phone, "*{$title}*\n\n{$message}\n\n_Sistem Kerjasama Daerah Samarinda_");
        } catch (\Exception $e) {
            Log::error('Reminder WA admin error: ' . $e->getMessage());
        }
    }

    protected function sendWaGroup(WhatsappService $whatsapp, string $title, string $message): void
    {
        try {
            $whatsapp->sendToGroup("*{$title}*\n\n{$message}\n\n_Sistem Kerjasama Daerah Samarinda_");
        } catch (\Exception $e) {
            Log::error('Reminder WA group error: ' . $e->getMessage());
        }
    }
}
