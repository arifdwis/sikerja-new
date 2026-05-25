<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use App\Models\PermohonanTtd;
use App\Models\PermohonanHistori;
use App\Models\Notifikasi;
use App\Services\WhatsappService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PenandatangananController extends Controller
{
    /**
     * Pemohon upload dokumen yang sudah ditandatangani + checklist (paraf, materai, stempel).
     */
    public function uploadTtd(Request $request, string $uuid)
    {
        $permohonan = Permohonan::uuid($uuid)->firstOrFail();

        if (Auth::user()->hasRole('pemohon') && $permohonan->id_pemohon_0 != Auth::id()) {
            abort(403);
        }

        if ($permohonan->status != Permohonan::STATUS_MENUNGGU_TANDATANGAN) {
            abort(403, 'Upload dokumen tertandatangani hanya tersedia setelah penandatanganan.');
        }

        $validated = $request->validate([
            'file' => 'required|file|mimes:pdf|max:20480',
            'checklist_paraf' => 'required|accepted',
            'checklist_materai' => 'required|accepted',
            'checklist_stempel' => 'required|accepted',
        ], [
            'file.required' => 'File dokumen tertandatangani wajib diupload.',
            'file.mimes' => 'File harus berformat PDF.',
            'file.max' => 'Ukuran file maksimal 20MB.',
            'checklist_paraf.accepted' => 'Centang paraf basah wajib dicentang.',
            'checklist_materai.accepted' => 'Centang materai wajib dicentang.',
            'checklist_stempel.accepted' => 'Centang stempel wajib dicentang.',
        ]);

        $file = $request->file('file');
        $filename = time() . '_TTD_' . Str::slug($permohonan->kode) . '.pdf';
        $path = $file->storeAs('uploads/ttd/' . $permohonan->id, $filename, 'public');

        PermohonanTtd::create([
            'id_permohonan' => $permohonan->id,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'tipe' => PermohonanTtd::TIPE_PEMOHON,
            'checklist_paraf' => true,
            'checklist_materai' => true,
            'checklist_stempel' => true,
            'is_validated' => false,
            'uploaded_by' => Auth::id(),
        ]);

        // Update status ke PASCA_TANDATANGAN (5)
        $permohonan->update(['status' => Permohonan::STATUS_PASCA_TANDATANGAN]);

        PermohonanHistori::create([
            'id_permohonan' => $permohonan->id,
            'id_operator' => Auth::id(),
            'role_operator' => Auth::user()->role_name,
            'deskripsi' => 'Pemohon mengupload dokumen tertandatangani. Menunggu validasi admin.',
        ]);

        // Notif WA ke admin group — pakai template internal formal
        try {
            $wa = app(WhatsappService::class);
            $waMsg = \App\Services\NotificationTemplate::dokumenTtdDiterima(
                $permohonan,
                Auth::user()?->name ?? 'Pemohon'
            );
            $wa->sendToGroup($waMsg);
        } catch (\Exception $e) {
            Log::error('Failed WA notif: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Dokumen tertandatangani berhasil diupload. Menunggu validasi admin.');
    }

    /**
     * Admin validasi dokumen tertandatangani + upload versi admin.
     */
    public function validateTtd(Request $request, string $uuid)
    {
        $permohonan = Permohonan::uuid($uuid)->firstOrFail();

        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'ttd_id' => 'required|exists:permohonan_ttd,id',
            'is_valid' => 'required|boolean',
            'catatan_admin' => 'nullable|string|max:1000',
            'admin_file' => 'nullable|file|mimes:pdf|max:20480',
        ]);

        $ttd = PermohonanTtd::findOrFail($validated['ttd_id']);

        if ($ttd->id_permohonan != $permohonan->id) {
            abort(400, 'Dokumen TTD tidak sesuai dengan permohonan.');
        }

        if ($validated['is_valid']) {
            // VALID: tandai tervalidasi, reset alasan_tolak (jika sebelumnya pernah ditolak)
            $ttd->update([
                'is_validated' => true,
                'validated_by' => Auth::id(),
                'validated_at' => now(),
                'catatan_admin' => $validated['catatan_admin'] ?? null,
            ]);

            if ($permohonan->alasan_tolak) {
                $permohonan->update(['alasan_tolak' => null]);
            }

            // Admin upload dokumen identik (versi admin) untuk diberikan ke pemohon
            if ($request->hasFile('admin_file')) {
                $file = $request->file('admin_file');
                $filename = time() . '_TTD_ADMIN_' . Str::slug($permohonan->kode) . '.pdf';
                $path = $file->storeAs('uploads/ttd/' . $permohonan->id, $filename, 'public');

                PermohonanTtd::create([
                    'id_permohonan' => $permohonan->id,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'tipe' => PermohonanTtd::TIPE_ADMIN,
                    'checklist_paraf' => true,
                    'checklist_materai' => true,
                    'checklist_stempel' => true,
                    'is_validated' => true,
                    'validated_by' => Auth::id(),
                    'validated_at' => now(),
                    'uploaded_by' => Auth::id(),
                ]);
            }
        } else {
            // TIDAK VALID: hapus file pemohon, rollback status ke MENUNGGU_TANDATANGAN (4)
            // sehingga pemohon bisa upload ulang.
            try {
                if ($ttd->file_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($ttd->file_path)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($ttd->file_path);
                }
            } catch (\Throwable $e) {
                // log silently
            }
            $ttd->delete();

            // Simpan alasan tolak di permohonan supaya bisa ditampilkan sebagai banner
            $alasan = trim($validated['catatan_admin'] ?? '');
            $permohonan->update([
                'status' => Permohonan::STATUS_MENUNGGU_TANDATANGAN,
                'alasan_tolak' => $alasan !== '' ? $alasan : 'Dokumen tertandatangani perlu diperbaiki dan diupload ulang.',
            ]);
        }

        $deskripsi = $validated['is_valid']
            ? 'Admin memvalidasi dokumen tertandatangani.'
            : 'Admin menolak dokumen tertandatangani: ' . ($validated['catatan_admin'] ?? '-');

        PermohonanHistori::create([
            'id_permohonan' => $permohonan->id,
            'id_operator' => Auth::id(),
            'role_operator' => Auth::user()->role_name,
            'deskripsi' => $deskripsi,
        ]);

        // Notif ke pemohon (system + WA) — pakai template formal
        $pemohonId = $permohonan->id_pemohon_0;
        $pemohonUser = \App\Models\User::find($pemohonId);
        $pemohonName = $permohonan->pemohon1?->name ?: ($pemohonUser?->name ?: 'Pemohon');

        if ($validated['is_valid']) {
            // VALID → tahap pelaksanaan akan dipicu observer saat status berubah ke STATUS_PELAKSANAAN.
            // Di sini hanya kirim konfirmasi singkat bahwa dokumen tervalidasi.
            $payload = \App\Services\NotificationTemplate::statusUpdate(
                $pemohonName,
                $permohonan,
                'Dokumen Tertandatangani Tervalidasi',
                'Dokumen tertandatangani Bpk/Ibu telah divalidasi oleh admin. Menunggu persetujuan PKS final untuk masuk tahap pelaksanaan kerja sama.'
            );
        } else {
            $payload = \App\Services\NotificationTemplate::ttdDitolak(
                $pemohonName,
                $permohonan,
                $validated['catatan_admin'] ?: 'Mohon Bpk/Ibu memeriksa kembali kelengkapan dokumen sebelum mengunggah ulang.'
            );
        }

        Notifikasi::create([
            'id_user'  => $pemohonId,
            'category' => 'permohonan',
            'title'    => $payload['system']['title'],
            'message'  => $payload['system']['message'],
            'data'     => json_encode(['uuid' => $permohonan->uuid]),
            'is_read'  => false,
        ]);

        try {
            $wa = app(WhatsappService::class);
            if ($pemohonUser?->phone) {
                $wa->sendMessage($pemohonUser->phone, $payload['wa']);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('WA notif TTD validate error: ' . $e->getMessage());
        }

        return redirect()->back()->with(
            'success',
            $validated['is_valid']
                ? 'Validasi dokumen tertandatangani berhasil.'
                : 'Dokumen ditolak. Pemohon perlu upload ulang dokumen tertandatangani.'
        );
    }
}
