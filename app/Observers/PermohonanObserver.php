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
     * yang sama dua kali dalam satu request lifecycle. Mencegah double WA saat status &
     * jangka_waktu berubah bersamaan.
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

        // 1. Catat histori awal
        PermohonanHistori::create([
            'id_permohonan' => $permohonan->id,
            'id_operator'   => auth()->id() ?? $permohonan->id_pemohon_0,
            'deskripsi'     => 'Permohonan kerja sama dibuat.',
        ]);

        // 2. Notifikasi sistem ke admin / TKKSD (admin = whitelist via scope)
        $admins = User::adminNotificationRecipients()->get();
        $tkksdUsers = User::whereHas('roles', fn($q) => $q->where('slug', 'tkksd'))->get();
        $recipients = $admins->merge($tkksdUsers)->unique('id');

        foreach ($recipients as $admin) {
            Notifikasi::create([
                'id_user'  => $admin->id,
                'category' => 'permohonan',
                'title'    => 'Permohonan Baru Masuk',
                'message'  => "Permohonan baru dari {$permohonan->nama_instansi} menunggu validasi awal.",
                'data'     => json_encode(['uuid' => $permohonan->uuid]),
                'is_read'  => false,
            ]);
        }

        // 3. WA grup admin — pakai template formal
        $oleh = auth()->user()?->name ?? ($permohonan->pemohon?->name ?? 'Pemohon');
        $this->waService->sendToGroup(NotificationTemplate::permohonanBaruDariPemohon($permohonan, $oleh));
    }

    /**
     * Handle the Permohonan "updated" event.
     */
    public function updated(Permohonan $permohonan): void
    {
        // Log perubahan jangka waktu (Req 5.5, 5.6) — Admin edit pasca-pembahasan
        $this->handleJangkaWaktuChange($permohonan);

        if ($permohonan->isDirty('status')) {
            $oldStatus = (int) $permohonan->getOriginal('status');
            $newStatus = (int) $permohonan->status;
            $user = auth()->user();

            $deskripsi = $this->getDeskripsiForStatus($newStatus);
            if (in_array($newStatus, [Permohonan::STATUS_DITOLAK, Permohonan::STATUS_DICABUT, 99])) {
                $deskripsi .= ': ' . ($permohonan->alasan_tolak ?? 'Tidak ada alasan spesifik.');
            }

            // 1. Histori
            PermohonanHistori::create([
                'id_permohonan' => $permohonan->id,
                'id_operator'   => $user?->id,
                'role_operator' => $user?->role_name,
                'deskripsi'     => $deskripsi,
            ]);

            // 2. Notifikasi pemohon (system + WA) sesuai tahapan
            $this->notifyPemohonForStatus($permohonan, $newStatus);
        }
    }

    /**
     * Build dan kirim notifikasi pemohon sesuai tahapan baru.
     */
    private function notifyPemohonForStatus(Permohonan $permohonan, int $newStatus): void
    {
        $pemohonId = $permohonan->id_pemohon_0;
        if (!$pemohonId) {
            return;
        }

        // Anti-duplicate per request lifecycle
        $key = "{$permohonan->id}_{$newStatus}";
        if (in_array($key, self::$notifiedThisRequest, true)) {
            return;
        }
        self::$notifiedThisRequest[] = $key;

        $pemohonUser = User::find($pemohonId);
        $pemohonProfile = $permohonan->pemohon;
        $namaPemohon = $pemohonProfile?->name ?: ($pemohonUser?->name ?: 'Pemohon');

        $payload = match ($newStatus) {
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

        // Sistem
        Notifikasi::create([
            'id_user'  => $pemohonId,
            'category' => 'permohonan',
            'title'    => $payload['system']['title'],
            'message'  => $payload['system']['message'],
            'data'     => json_encode(['uuid' => $permohonan->uuid]),
            'is_read'  => false,
        ]);

        // WA pemohon
        $targetPhone = $pemohonUser?->phone ?: $pemohonProfile?->phone;
        if ($targetPhone) {
            $this->waService->sendMessage($targetPhone, $payload['wa']);
        }

        // Internal grup admin (rekap update status)
        $this->waService->sendToGroup(
            NotificationTemplate::internalGroup(
                'Pembaruan Status Permohonan',
                $permohonan,
                "Status permohonan kerja sama berubah menjadi *" . ($permohonan->status_label['label'] ?? '-') . "*.",
                auth()->user()?->name,
                in_array($newStatus, [Permohonan::STATUS_DITOLAK, Permohonan::STATUS_DICABUT], true)
                    ? $permohonan->alasan_tolak
                    : null
            )
        );

        // Jika status dicabut, informasikan juga ke TKKSD Lokus OPD terkait.
        if ($newStatus === Permohonan::STATUS_DICABUT) {
            $this->notifyTkksdLokusPencabutan($permohonan);
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
                'data'     => json_encode(['uuid' => $permohonan->uuid]),
                'is_read'  => false,
            ]);

            if (!empty($u->phone)) {
                $this->waService->sendMessage($u->phone, $message);
            }
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

        // Hanya log jika permohonan sudah melewati pembahasan
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
                'data'          => json_encode(['uuid' => $permohonan->uuid, 'changes' => $changedFields]),
                'is_read'       => false,
            ]);

            if ($pemohonUser?->phone) {
                $this->waService->sendMessage($pemohonUser->phone, $payload['wa']);
            }
        } catch (\Exception $e) {
            Log::error('handleJangkaWaktuChange notif/WA: ' . $e->getMessage());
        }
    }

    /**
     * Get deskripsi for history based on status
     */
    private function getDeskripsiForStatus($status): string
    {
        return match ((int) $status) {
            Permohonan::STATUS_PERMOHONAN           => 'Permohonan dikembalikan / revisi.',
            Permohonan::STATUS_PEMBAHASAN           => 'Permohonan divalidasi dan masuk pembahasan.',
            Permohonan::STATUS_PENJADWALAN          => 'Pembahasan selesai. Pemohon dipersilakan mengajukan jadwal penandatanganan.',
            Permohonan::STATUS_JADWAL_DISETUJUI     => 'Jadwal penandatanganan disetujui. Admin akan menyiapkan PKS final.',
            Permohonan::STATUS_MENUNGGU_TANDATANGAN => 'PKS final telah disiapkan admin. Menunggu hari penandatanganan.',
            Permohonan::STATUS_PASCA_TANDATANGAN    => 'Dokumen tertandatangani diunggah. Menunggu validasi admin.',
            Permohonan::STATUS_PELAKSANAAN          => 'Dokumen disetujui. Kerja sama memasuki tahap pelaksanaan.',
            Permohonan::STATUS_SELESAI              => 'Kerja sama telah selesai.',
            Permohonan::STATUS_DICABUT              => 'Kerja sama dicabut pada tahap pelaksanaan.',
            Permohonan::STATUS_DITOLAK              => 'Permohonan dikembalikan untuk direvisi',
            default                                 => 'Status permohonan diperbarui.',
        };
    }
}
