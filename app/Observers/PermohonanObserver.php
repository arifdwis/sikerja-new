<?php

namespace App\Observers;

use App\Models\Permohonan;
use App\Models\PermohonanHistori;
use App\Models\Notifikasi;
use App\Models\User;
use App\Services\NotificationTemplate;
use App\Services\WhatsappService;
use Illuminate\Support\Facades\Log;

class PermohonanObserver
{
    protected $waService;

    /**
     * Anti-duplicate guard: jangan kirim notif dengan kombinasi (permohonan_id + status_baru)
     * yang sama dua kali dalam satu request lifecycle.
     */
    private static array $notifiedThisRequest = [];

    public function __construct(WhatsappService $waService)
    {
        $this->waService = $waService;
    }

    /**
     * Handle the Permohonan "created" event.
     */
    public function created(Permohonan $permohonan): void
    {
        Log::info('PermohonanObserver: created event fired', ['id' => $permohonan->id]);

        PermohonanHistori::create([
            'id_permohonan' => $permohonan->id,
            'id_operator'   => auth()->id() ?? $permohonan->id_pemohon_0,
            'deskripsi'     => ($permohonan->is_draft || $permohonan->status == Permohonan::STATUS_DRAFT)
                ? 'Draft permohonan kerjasama disimpan.'
                : 'Permohonan kerjasama resmi diajukan.',
        ]);

        if ($permohonan->is_draft || $permohonan->status == Permohonan::STATUS_DRAFT) {
            return;
        }

        // Notifikasi sistem ke admin / Kabag / TKKSD
        $admins = User::adminNotificationRecipients()->get();
        $tkksdUsers = User::whereHas('roles', fn($q) => $q->whereIn('slug', ['tkksd', 'kabag_kerjasama']))->get();
        $recipients = $admins->merge($tkksdUsers)->unique('id');

        foreach ($recipients as $admin) {
            Notifikasi::create([
                'id_user'  => $admin->id,
                'category' => 'permohonan',
                'title'    => 'Permohonan Baru Masuk',
                'message'  => "Permohonan baru dari {$permohonan->nama_instansi} menunggu verifikasi administrasi.",
                'data'     => ['uuid' => $permohonan->uuid],
                'is_read'  => false,
            ]);
        }

        // WA grup admin
        $oleh = auth()->user()?->name ?? ($permohonan->pemohon?->name ?? 'Pemohon');
        $this->waService->sendToGroup(NotificationTemplate::permohonanBaruDariPemohon($permohonan, $oleh));
    }

    /**
     * Handle the Permohonan "updated" event.
     */
    public function updated(Permohonan $permohonan): void
    {
        $this->handleJangkaWaktuChange($permohonan);

        if ($permohonan->isDirty('status')) {
            $oldStatus = (int) $permohonan->getOriginal('status');
            $newStatus = (int) $permohonan->status;
            $user = auth()->user();

            $deskripsi = $this->getDeskripsiForStatus($newStatus);
            if (in_array($newStatus, [Permohonan::STATUS_DITOLAK, Permohonan::STATUS_DICABUT, Permohonan::STATUS_REVISI, 99])) {
                $deskripsi .= ': ' . ($permohonan->alasan_tolak ?? 'Tidak ada alasan spesifik.');
            }

            PermohonanHistori::create([
                'id_permohonan' => $permohonan->id,
                'id_operator'   => $user?->id,
                'role_operator' => $user?->role_name,
                'deskripsi'     => $deskripsi,
            ]);

            $this->notifyPemohonForStatus($permohonan, $newStatus);
        }
    }

    /**
     * Build dan kirim notifikasi pemohon & pejabat terkait sesuai tahapan baru.
     */
    private function notifyPemohonForStatus(Permohonan $permohonan, int $newStatus): void
    {
        $pemohonId = $permohonan->id_pemohon_0;
        if (!$pemohonId) {
            return;
        }

        $key = "{$permohonan->id}_{$newStatus}";
        if (in_array($key, self::$notifiedThisRequest, true)) {
            return;
        }
        self::$notifiedThisRequest[] = $key;

        $pemohonUser = User::find($pemohonId);
        $pemohonProfile = $permohonan->pemohon;
        $namaPemohon = $pemohonProfile?->name ?: ($pemohonUser?->name ?: 'Pemohon');

        $payload = match ($newStatus) {
            Permohonan::STATUS_VERIFIKASI_ADMIN     => NotificationTemplate::statusUpdate(
                $namaPemohon,
                $permohonan,
                'Lolos Verifikasi Staf — Menunggu Validasi Kabag Kerja Sama',
                'Berkas administrasi Anda telah diverifikasi lengkap oleh staf dan diteruskan ke Kepala Bagian Kerja Sama untuk validasi dan disposisi.'
            ),
            Permohonan::STATUS_REVISI               => NotificationTemplate::permohonanDitolak(
                $namaPemohon,
                $permohonan,
                $permohonan->alasan_tolak ?? 'Mohon lengkapi berkas atau perbaiki data sesuai catatan verifikator.'
            ),
            Permohonan::STATUS_PEMBAHASAN           => NotificationTemplate::permohonanDiterima($namaPemohon, $permohonan),
            Permohonan::STATUS_PENJADWALAN          => NotificationTemplate::pembahasanSelesai($namaPemohon, $permohonan),
            Permohonan::STATUS_PELAKSANAAN          => NotificationTemplate::pelaksanaanDimulai($namaPemohon, $permohonan),
            Permohonan::STATUS_DICABUT              => NotificationTemplate::kerjasamaDicabut($namaPemohon, $permohonan, $permohonan->alasan_tolak ?? '-'),
            Permohonan::STATUS_DITOLAK              => NotificationTemplate::permohonanDitolak($namaPemohon, $permohonan, $permohonan->alasan_tolak ?? '-'),
            default                                 => NotificationTemplate::statusUpdate(
                $namaPemohon,
                $permohonan,
                $permohonan->status_label['label'] ?? 'Status Baru',
            ),
        };

        // Sistem ke Pemohon
        Notifikasi::create([
            'id_user'  => $pemohonId,
            'category' => 'permohonan',
            'title'    => $payload['system']['title'],
            'message'  => $payload['system']['message'],
            'data'     => ['uuid' => $permohonan->uuid],
            'is_read'  => false,
        ]);

        // WA/Email ke Pemohon
        $targetRecipient = $pemohonUser ?: ($permohonan->email ?: $pemohonProfile);
        if ($targetRecipient) {
            $this->waService->sendMessage($targetRecipient, $payload['wa']);
        }

        // Jika status = 10 (Menunggu Validasi Kabag), kirim notifikasi khusus ke akun Kabag Kerja Sama
        if ($newStatus === Permohonan::STATUS_VERIFIKASI_ADMIN) {
            $this->notifyKabagKerjasama($permohonan);
        }

        // Internal grup admin
        $this->waService->sendToGroup(
            NotificationTemplate::internalGroup(
                'Pembaruan Status Permohonan',
                $permohonan,
                "Status permohonan kerja sama berubah menjadi *" . ($permohonan->status_label['label'] ?? '-') . "*.",
                auth()->user()?->name,
                in_array($newStatus, [Permohonan::STATUS_DITOLAK, Permohonan::STATUS_DICABUT, Permohonan::STATUS_REVISI], true)
                    ? $permohonan->alasan_tolak
                    : null
            )
        );

        if ($newStatus === Permohonan::STATUS_DICABUT) {
            $this->notifyTkksdLokusPencabutan($permohonan);
        }
    }

    /**
     * Notifikasi khusus ke Kepala Bagian Kerja Sama saat berkas lolos verifikasi staf (Status 10).
     */
    private function notifyKabagKerjasama(Permohonan $permohonan): void
    {
        $kabagUsers = User::whereHas('roles', fn($q) => $q->where('slug', 'kabag_kerjasama'))->get();
        if ($kabagUsers->isEmpty()) {
            return;
        }

        $title = 'Menunggu Validasi & Disposisi Kabag';
        $message = "Permohonan \"{$permohonan->label}\" dari {$permohonan->nama_instansi} telah diverifikasi staf dan menunggu validasi/disposisi Kepala Bagian Kerja Sama.";

        foreach ($kabagUsers as $kabag) {
            Notifikasi::create([
                'id_user'       => $kabag->id,
                'id_permohonan' => $permohonan->id,
                'from_user_id'  => auth()->id(),
                'category'      => 'permohonan',
                'type'          => 'validasi_kabag',
                'title'         => $title,
                'message'       => $message,
                'data'          => ['uuid' => $permohonan->uuid],
                'is_read'       => false,
            ]);

            try {
                $waMsg = NotificationTemplate::internalGroup(
                    $title,
                    $permohonan,
                    $message,
                    auth()->user()?->name ?? 'Staf Bagian Kerja Sama'
                );
                $this->waService->sendMessage($kabag, $waMsg);
            } catch (\Throwable $e) {
                Log::warning('Gagal kirim WA ke Kabag: ' . $e->getMessage());
            }
        }
    }

    private function notifyTkksdLokusPencabutan(Permohonan $permohonan): void
    {
        $opdIds = $permohonan->opds()->pluck('opd.id')->toArray();
        if (empty($opdIds)) return;

        $users = User::whereHas('roles', fn($q) => $q->where('slug', 'tkksd_lokus'))
            ->whereIn('id_opd', $opdIds)
            ->get();

        if ($users->isEmpty()) return;

        $title = 'Kerjasama Dicabut';
        $message = 'Kerjasama "' . $permohonan->label . '" dicabut pada tahap pelaksanaan. Alasan: ' . ($permohonan->alasan_tolak ?? '-');

        foreach ($users as $u) {
            Notifikasi::create([
                'id_user'  => $u->id,
                'category' => 'permohonan',
                'title'    => $title,
                'message'  => $message,
                'data'     => ['uuid' => $permohonan->uuid],
                'is_read'  => false,
            ]);

            $this->waService->sendMessage($u, $message);
        }
    }

    /**
     * Handle perubahan jangka waktu pasca-pembahasan (Req 5.5, 5.6).
     */
    protected function handleJangkaWaktuChange(Permohonan $permohonan): void
    {
        $jangkaFields = ['tanggal_mulai', 'tanggal_berakhir', 'jangka_waktu'];
        $changedFields = [];

        foreach ($jangkaFields as $field) {
            if ($permohonan->isDirty($field)) {
                $changedFields[$field] = [
                    'old' => $permohonan->getOriginal($field),
                    'new' => $permohonan->{$field},
                ];
            }
        }

        if (empty($changedFields)) {
            return;
        }

        $oldStatus = (int) $permohonan->getOriginal('status');
        if ($oldStatus < Permohonan::STATUS_PENJADWALAN) {
            return;
        }

        $user = auth()->user();
        $labels = [
            'tanggal_mulai'    => 'Tanggal Mulai',
            'tanggal_berakhir' => 'Tanggal Berakhir',
            'jangka_waktu'     => 'Jangka Waktu',
        ];
        $details = [];
        foreach ($changedFields as $field => $values) {
            $details[] = "{$labels[$field]}: " . ($values['old'] ?: '-') . " → " . ($values['new'] ?: '-');
        }

        PermohonanHistori::create([
            'id_permohonan' => $permohonan->id,
            'id_operator'   => $user?->id,
            'role_operator' => $user?->role_name,
            'deskripsi'     => 'Perubahan jangka waktu kerja sama oleh admin.',
            'komentar'      => implode('; ', $details),
        ]);

        try {
            $pemohonId = $permohonan->id_pemohon_0;
            $pemohonUser = User::find($pemohonId);
            $namaPemohon = $permohonan->pemohon?->name ?: ($pemohonUser?->name ?: 'Pemohon');

            $payload = NotificationTemplate::statusUpdate(
                $namaPemohon,
                $permohonan,
                'Perubahan Jangka Waktu',
                "Detail perubahan:\n" . implode("\n", $details)
            );

            Notifikasi::create([
                'id_user'       => $pemohonId,
                'id_permohonan' => $permohonan->id,
                'from_user_id'  => $user?->id,
                'type'          => 'jangka_waktu_change',
                'title'         => 'Perubahan Jangka Waktu Kerja Sama',
                'message'       => $payload['system']['message'],
                'data'          => ['uuid' => $permohonan->uuid, 'changes' => $changedFields],
                'is_read'       => false,
            ]);

            $targetRecipient = $pemohonUser ?: ($pemohonUser?->phone ?: $permohonan->pemohon);
            if ($targetRecipient) {
                $this->waService->sendMessage($targetRecipient, $payload['wa']);
            }
        } catch (\Exception $e) {
            Log::error('handleJangkaWaktuChange notif/WA: ' . $e->getMessage());
        }
    }

    private function getDeskripsiForStatus($status): string
    {
        return match ((int) $status) {
            Permohonan::STATUS_PERMOHONAN           => 'Permohonan diajukan / menunggu verifikasi administrasi.',
            Permohonan::STATUS_VERIFIKASI_ADMIN     => 'Berkas diverifikasi staf dan diteruskan ke Kepala Bagian Kerja Sama.',
            Permohonan::STATUS_REVISI               => 'Permohonan dikembalikan ke pemohon untuk perbaikan/revisi.',
            Permohonan::STATUS_TERVERIFIKASI        => 'Permohonan disetujui Kepala Bagian Kerja Sama.',
            Permohonan::STATUS_PEMBAHASAN           => 'Permohonan divalidasi dan masuk pembahasan TKKSD.',
            Permohonan::STATUS_PENJADWALAN          => 'Pembahasan selesai. Pemohon dipersilakan mengajukan jadwal penandatanganan.',
            Permohonan::STATUS_JADWAL_DISETUJUI     => 'Jadwal penandatanganan disetujui. Admin akan menyiapkan naskah final.',
            Permohonan::STATUS_MENUNGGU_TANDATANGAN => 'Naskah final telah disiapkan admin. Menunggu penandatanganan.',
            Permohonan::STATUS_PASCA_TANDATANGAN    => 'Dokumen tertandatangani diunggah. Menunggu validasi admin.',
            Permohonan::STATUS_PELAKSANAAN          => 'Dokumen disetujui. Kerja sama memasuki tahap pelaksanaan.',
            Permohonan::STATUS_EVALUASI             => 'Kerja sama memasuki masa evaluasi akhir (H-90 hari).',
            Permohonan::STATUS_PERPANJANGAN         => 'Usulan perpanjangan kerja sama diajukan.',
            Permohonan::STATUS_SELESAI              => 'Kerja sama telah selesai.',
            Permohonan::STATUS_DICABUT              => 'Kerja sama dicabut pada tahap pelaksanaan.',
            Permohonan::STATUS_DITOLAK              => 'Permohonan ditolak.',
            default                                 => 'Status permohonan diperbarui.',
        };
    }
}
