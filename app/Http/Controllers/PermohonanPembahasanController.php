<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use App\Models\PermohonanPembahasan;
use App\Models\PermohonanFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class PermohonanPembahasanController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
        ];
    }

    /**
     * Get discussion history for a permohonan
     */
    public function index(string $uuid)
    {
        $permohonan = Permohonan::where('uuid', $uuid)->firstOrFail();

        // Check access
        $user = Auth::user();
        if ($user->hasRole('pemohon') && $permohonan->id_pemohon_0 != $user->id) {
            abort(403);
        }

        $discussions = PermohonanPembahasan::with(['operator:id,name', 'file'])
            ->where('id_permohonan', $permohonan->id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($chat) use ($user) {
                $chat->is_me = $chat->id_operator === $user->id;
                $chat->formatted_time = $chat->created_at->format('d M H:i');
                return $chat;
            });

        return response()->json($discussions);
    }

    /**
     * Store new message or file
     */
    public function store(Request $request, string $uuid)
    {
        $permohonan = Permohonan::where('uuid', $uuid)->firstOrFail();
        $user = Auth::user();

        if ($user->hasRole('pemohon') && $permohonan->id_pemohon_0 != $user->id) {
            abort(403);
        }

        $request->validate([
            'message' => 'nullable|string',
            'file' => 'nullable|file|max:10240', // Max 10MB
            'file_label' => 'nullable|string'
        ]);

        if (!$request->message && !$request->hasFile('file')) {
            return response()->json(['error' => 'Pesan atau file harus diisi'], 422);
        }

        $idFile = null;

        // Handle File Upload
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store("documents/pembahasan/{$permohonan->uuid}", 'public');

            $fileRecord = PermohonanFile::create([
                'id_permohonan' => $permohonan->id,
                'label' => $request->file_label ?? $file->getClientOriginalName(),
                'file' => $path,
                'deskripsi' => 'Diunggah melalui diskusi pembahasan',
                'status' => PermohonanFile::STATUS_DIPROSES
            ]);

            $idFile = $fileRecord->id;
        }

        // Create Pembahasan Entry
        $pembahasan = PermohonanPembahasan::create([
            'id_permohonan' => $permohonan->id,
            'id_operator' => $user->id,
            'komentar' => $request->message ?: 'Mengunggah dokumen',
            'id_file' => $idFile,
        ]);

        // Send notification to the other party
        $this->sendDiscussionNotification($permohonan, $user, $pembahasan);

        // Eager load for quick return
        $pembahasan->load(['operator:id,name', 'file']);
        $pembahasan->is_me = true;
        $pembahasan->formatted_time = $pembahasan->created_at->format('d M H:i');

        return response()->json($pembahasan);
    }

    /**
     * Get discussion history for a specific file
     */
    public function fileIndex(string $fileUuid)
    {
        $file = PermohonanFile::where('uuid', $fileUuid)->firstOrFail();
        $permohonan = $file->permohonan;
        $user = Auth::user();

        // Check access
        if ($user->hasRole('pemohon') && $permohonan->id_pemohon_0 != $user->id) {
            abort(403);
        }

        $discussions = PermohonanPembahasan::with(['operator:id,name'])
            ->where('id_file', $file->id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($chat) use ($user) {
                $chat->is_me = $chat->id_operator === $user->id;
                $chat->formatted_time = $chat->created_at->format('d M H:i');
                return $chat;
            });

        return response()->json($discussions);
    }

    /**
     * Store new message for a specific file
     */
    public function fileStore(Request $request, string $fileUuid)
    {
        $file = PermohonanFile::where('uuid', $fileUuid)->firstOrFail();
        $permohonan = $file->permohonan;
        $user = Auth::user();

        if ($user->hasRole('pemohon') && $permohonan->id_pemohon_0 != $user->id) {
            abort(403);
        }

        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $pembahasan = PermohonanPembahasan::create([
            'id_permohonan' => $permohonan->id,
            'id_operator' => $user->id,
            'id_file' => $file->id,
            'komentar' => $request->message,
        ]);

        // Send notification
        $this->sendFileDiscussionNotification($permohonan, $file, $user, $pembahasan);

        $pembahasan->load(['operator:id,name']);
        $pembahasan->is_me = true;
        $pembahasan->formatted_time = $pembahasan->created_at->format('d M H:i');

        return response()->json($pembahasan);
    }

    /**
     * Send notification for file-specific discussion
     */
    private function sendFileDiscussionNotification($permohonan, $file, $sender, $pembahasan)
    {
        $senderIsAdmin = !$sender->hasRole('pemohon');

        if ($senderIsAdmin) {
            // Admin sent message - notify pemohon
            \App\Models\Notifikasi::create([
                'id_user' => $permohonan->id_pemohon_0,
                'id_permohonan' => $permohonan->id,
                'from_user_id' => $sender->id,
                'type' => 'diskusi_file',
                'title' => "Balasan untuk dokumen \"{$file->label}\"",
                'message' => "Ada balasan baru dari TKKSD/Admin untuk dokumen \"{$file->label}\"",
            ]);
        } else {
            // Pemohon sent message - notify admins
            $adminIds = \App\Models\User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['admin', 'tkksd', 'verifikator']);
            })->pluck('id');

            foreach ($adminIds as $adminId) {
                \App\Models\Notifikasi::create([
                    'id_user' => $adminId,
                    'id_permohonan' => $permohonan->id,
                    'from_user_id' => $sender->id,
                    'type' => 'diskusi_file',
                    'title' => "Pesan baru untuk dokumen \"{$file->label}\"",
                    'message' => "Pemohon mengirim pesan untuk dokumen \"{$file->label}\" pada permohonan {$permohonan->kode}",
                ]);
            }
        }
    }

    /**
     * Send notification to the other party in the discussion
     */
    private function sendDiscussionNotification($permohonan, $sender, $pembahasan)
    {
        $senderIsAdmin = !$sender->hasRole('pemohon');

        if ($senderIsAdmin) {
            // Admin/TKKSD sent message - notify the pemohon
            $recipientId = $permohonan->id_pemohon_0;
            $message = "Ada balasan baru dari Admin/TKKSD untuk permohonan {$permohonan->kode}";
        } else {
            // Pemohon sent message - notify all admins (or specific admin if assigned)
            $adminIds = \App\Models\User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['admin', 'tkksd', 'verifikator']);
            })->pluck('id');
            foreach ($adminIds as $adminId) {
                \App\Models\Notifikasi::create([
                    'id_user' => $adminId,
                    'id_permohonan' => $permohonan->id,
                    'from_user_id' => $sender->id,
                    'type' => 'diskusi',
                    'title' => 'Pesan Baru dari Pemohon',
                    'message' => "Pemohon {$sender->name} mengirim pesan untuk permohonan {$permohonan->kode}",
                ]);
            }
            return;
        }

        // Create notification for pemohon
        \App\Models\Notifikasi::create([
            'id_user' => $recipientId,
            'id_permohonan' => $permohonan->id,
            'from_user_id' => $sender->id,
            'type' => 'diskusi',
            'title' => 'Balasan Diskusi Permohonan',
            'message' => $message,
        ]);
    }

    /**
     * Approve or reject a file
     */
    public function reviewFile(Request $request, string $fileUuid)
    {
        $user = Auth::user();

        // Only admin/tkksd can review files
        if ($user->hasRole('pemohon')) {
            abort(403, 'Hanya TKKSD/Admin yang dapat mereview dokumen.');
        }

        $request->validate([
            'status' => 'required|in:1,2', // 1 = approved, 2 = rejected
            'komentar' => 'nullable|string|max:500',
        ]);

        $file = PermohonanFile::where('uuid', $fileUuid)->firstOrFail();
        $permohonan = $file->permohonan;

        $statusLabel = $request->status == 1 ? 'Disetujui' : 'Ditolak';

        $file->update([
            'status' => $request->status,
            'komentar' => $request->komentar,
        ]);

        // Create a discussion entry for the file review
        PermohonanPembahasan::create([
            'id_permohonan' => $permohonan->id,
            'id_operator' => $user->id,
            'id_file' => $file->id,
            'komentar' => "Dokumen \"{$file->label}\" {$statusLabel}. " . ($request->komentar ? "Catatan: {$request->komentar}" : ''),
        ]);

        // Notify the pemohon
        \App\Models\Notifikasi::create([
            'id_user' => $permohonan->id_pemohon_0,
            'id_permohonan' => $permohonan->id,
            'from_user_id' => $user->id,
            'type' => 'dokumen',
            'title' => "Dokumen {$statusLabel}",
            'message' => "Dokumen \"{$file->label}\" untuk permohonan {$permohonan->kode} telah {$statusLabel}." . ($request->komentar ? " Catatan: {$request->komentar}" : ''),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Dokumen berhasil {$statusLabel}",
            'file' => $file->fresh()
        ]);
    }

    /**
     * Upload a revision/corrected file (Pemohon only - when file is rejected)
     */
    public function uploadRevision(Request $request, string $fileUuid)
    {
        $file = PermohonanFile::where('uuid', $fileUuid)->firstOrFail();
        $permohonan = $file->permohonan;
        $user = Auth::user();

        // Only pemohon can upload revision
        if ($permohonan->id_pemohon_0 != $user->id) {
            abort(403, 'Hanya pemohon yang dapat mengupload berkas perbaikan');
        }

        // Only rejected files can be revised
        if ($file->status != 2) {
            return response()->json(['error' => 'Hanya berkas yang ditolak yang dapat direvisi'], 422);
        }

        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
        ]);

        // Store the new file
        $uploadedFile = $request->file('file');
        $path = $uploadedFile->store('permohonan/files', 'public');

        // Update the file record
        $oldFilePath = $file->file_path;
        $file->update([
            'file_path' => $path,
            'file_name' => $uploadedFile->getClientOriginalName(),
            'file_url' => asset('storage/' . $path),
            'status' => 0, // Reset to pending review
            'komentar' => null, // Clear previous rejection comment
        ]);

        // Delete old file
        if ($oldFilePath && \Illuminate\Support\Facades\Storage::disk('public')->exists($oldFilePath)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($oldFilePath);
        }

        // Create a discussion entry for the revision
        PermohonanPembahasan::create([
            'id_permohonan' => $permohonan->id,
            'id_operator' => $user->id,
            'id_file' => $file->id,
            'komentar' => "Pemohon mengupload berkas perbaikan untuk dokumen \"{$file->label}\"",
        ]);

        // Notify admins
        $adminIds = \App\Models\User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['admin', 'tkksd', 'verifikator']);
        })->pluck('id');

        foreach ($adminIds as $adminId) {
            \App\Models\Notifikasi::create([
                'id_user' => $adminId,
                'id_permohonan' => $permohonan->id,
                'from_user_id' => $user->id,
                'type' => 'revisi_dokumen',
                'title' => 'Berkas Perbaikan Diupload',
                'message' => "Pemohon mengupload berkas perbaikan untuk dokumen \"{$file->label}\" pada permohonan {$permohonan->kode}",
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Berkas perbaikan berhasil diupload',
            'file' => $file->fresh()
        ]);
    }
}
