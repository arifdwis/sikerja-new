<?php

namespace App\Observers;

use App\Models\Permohonan;
use App\Models\PermohonanHistori;
use App\Models\Notifikasi;
use App\Models\User;
use App\Services\WhatsappService;
use Illuminate\Support\Facades\Log;

class PermohonanObserver
{
    protected $waService;

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

        // 1. Create Initial History
        PermohonanHistori::create([
            'id_permohonan' => $permohonan->id,
            'id_operator' => auth()->id() ?? $permohonan->id_pemohon_0,
            'deskripsi' => 'Permohonan kerjasama dibuat.',
        ]);

        // 2. Notify TKKSD / Admin System Notification
        $admins = User::whereHas('roles', function ($q) {
            $q->whereIn('slug', ['administrator', 'tkksd']);
        })->get();

        foreach ($admins as $admin) {
            Notifikasi::create([
                'id_user' => $admin->id,
                'category' => 'permohonan',
                'title' => 'Permohonan Baru Masuk',
                'message' => "Permohonan baru dari {$permohonan->nama_instansi} menunggu validasi.",
                'data' => json_encode(['uuid' => $permohonan->uuid]),
                'is_read' => false,
            ]);
        }

        // 3. WA to Admin Group
        $group = config('services.whatsapp.group_id');
        if ($group) {
            $message = "*SIKERJA - Permohonan Baru*\n\n" .
                "Instansi: {$permohonan->nama_instansi}\n" .
                "Perihal: {$permohonan->label}\n" .
                "Status: Menunggu Validasi\n\n" .
                "Mohon segera divalidasi via dashboard.\n" .
                "_Sistem Kerja Sama Daerah_";
            $this->waService->sendMessage($group, $message);
        }
    }

    /**
     * Handle the Permohonan "updated" event.
     */
    public function updated(Permohonan $permohonan): void
    {
        if ($permohonan->isDirty('status')) {
            $oldStatus = $permohonan->getOriginal('status');
            $newStatus = $permohonan->status;
            $user = auth()->user();

            $deskripsi = $this->getDeskripsiForStatus($newStatus);
            if ($newStatus == 99 || $newStatus == 9) {
                $deskripsi .= ": " . ($permohonan->alasan_tolak ?? 'Tidak ada alasan spesifik.');
            }

            // 1. Create History
            PermohonanHistori::create([
                'id_permohonan' => $permohonan->id,
                'id_operator' => $user ? $user->id : null,
                'deskripsi' => $deskripsi,
            ]);

            // 2. Prepare WA Message Content
            $statusLabel = $permohonan->status_label['label'];
            $waMessageContent = "*SIKERJA - Update Status*\n\n" .
                "Instansi: {$permohonan->nama_instansi}\n" .
                "Perihal: {$permohonan->label}\n\n" .
                "Status Baru: *{$statusLabel}*\n" .
                "Keterangan: {$deskripsi}\n\n" .
                "_Sistem Kerja Sama Daerah_";

            // 3. Notify Pemohon (System & WA)
            $pemohonId = $permohonan->id_pemohon_0;
            if ($pemohonId) {
                // System Notification
                Notifikasi::create([
                    'id_user' => $pemohonId,
                    'category' => 'permohonan',
                    'title' => 'Status Permohonan Diperbarui',
                    'message' => "Permohonan Anda untuk {$permohonan->nama_instansi} kini berstatus: " . $statusLabel,
                    'data' => json_encode(['uuid' => $permohonan->uuid]),
                    'is_read' => false,
                ]);

                // WA Notification
                $pemohonUser = User::find($pemohonId);
                if ($pemohonUser && $pemohonUser->phone) {
                    // Personalized greeting for direct message
                    $personalMsg = "Halo Bpk/Ibu *{$pemohonUser->name}*,\n\n" . $waMessageContent;
                    $this->waService->sendMessage($pemohonUser->phone, $personalMsg);
                }
            }

            // 4. Notify Admin Group about the update
            $group = config('services.whatsapp.group_id');
            if ($group) {
                $this->waService->sendMessage($group, $waMessageContent);
            }
        }
    }

    /**
     * Get deskripsi for history based on status
     */
    private function getDeskripsiForStatus($status): string
    {
        return match ($status) {
            Permohonan::STATUS_PERMOHONAN => 'Permohonan dikembalikan / revisi.',
            Permohonan::STATUS_PEMBAHASAN => 'Permohonan divalidasi dan masuk pembahasan.',
            Permohonan::STATUS_PENJADWALAN => 'Pembahasan selesai, menunggu penjadwalan pertemuan.',
            Permohonan::STATUS_DISETUJUI => 'Jadwal pertemuan disetujui.',
            Permohonan::STATUS_SELESAI => 'Proses kerjasama selesai.',
            Permohonan::STATUS_DITOLAK => 'Permohonan ditolak',
            default => 'Status permohonan diperbarui.',
        };
    }
}
