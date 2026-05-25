<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use App\Models\PermohonanPks;
use App\Models\PermohonanHistori;
use App\Models\Notifikasi;
use App\Services\WhatsappService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PksController extends Controller
{
    /**
     * Admin upload PKS final di tahap STATUS_JADWAL_DISETUJUI (3).
     * Setelah upload, status berpindah ke MENUNGGU_TANDATANGAN (4).
     *
     * NOTE: Endpoint ini di-rename dari "store pemohon" menjadi admin-only sesuai workflow:
     * - Pemohon TIDAK upload PKS final.
     * - Pemohon hanya upload dokumen tertandatangani di tahap status 4 → 5 (lewat TtdController).
     */
    public function store(Request $request, string $uuid)
    {
        $permohonan = Permohonan::uuid($uuid)->firstOrFail();

        // Hanya admin
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Hanya admin yang dapat mengupload PKS final.');
        }

        // Status check
        if ($permohonan->status != Permohonan::STATUS_JADWAL_DISETUJUI) {
            abort(403, 'Upload PKS final hanya dapat dilakukan setelah jadwal penandatanganan disetujui.');
        }

        $validated = $request->validate([
            'file' => 'required|file|mimes:pdf|max:20480',
            'catatan' => 'nullable|string|max:1000',
        ], [
            'file.required' => 'File PKS wajib diupload.',
            'file.mimes' => 'File harus berformat PDF.',
            'file.max' => 'Ukuran file maksimal 20MB.',
        ]);

        $file = $request->file('file');
        $filename = time() . '_PKS_FINAL_' . Str::slug($permohonan->kode) . '.pdf';
        $path = $file->storeAs('uploads/pks/' . $permohonan->id, $filename, 'public');

        PermohonanPks::create([
            'id_permohonan' => $permohonan->id,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'tipe' => PermohonanPks::TIPE_ADMIN,
            'status' => PermohonanPks::STATUS_PENDING,
            'uploaded_by' => Auth::id(),
            'catatan' => $validated['catatan'] ?? null,
        ]);

        // Update status permohonan ke MENUNGGU_TANDATANGAN (4)
        $permohonan->update(['status' => Permohonan::STATUS_MENUNGGU_TANDATANGAN]);

        // Histori
        PermohonanHistori::create([
            'id_permohonan' => $permohonan->id,
            'id_operator' => Auth::id(),
            'role_operator' => Auth::user()->role_name,
            'deskripsi' => 'Admin mengupload PKS final. Permohonan menunggu penandatanganan.',
        ]);

        // Notif ke pemohon: PKS final sudah disiapkan, harap mempersiapkan dokumen untuk TTD
        $this->notifPemohonPksReady($permohonan);

        return redirect()->back()->with('success', 'PKS final berhasil diupload. Pemohon menunggu hari penandatanganan.');
    }

    /**
     * Admin upload PKS versi admin (setelah penandatanganan).
     */
    public function adminUpload(Request $request, string $uuid)
    {
        $permohonan = Permohonan::uuid($uuid)->firstOrFail();

        if (!Auth::user()->isAdmin()) {
            abort(403, 'Hanya admin yang dapat upload PKS versi admin.');
        }

        $validated = $request->validate([
            'file' => 'required|file|mimes:pdf|max:20480',
            'catatan' => 'nullable|string|max:1000',
        ]);

        $file = $request->file('file');
        $filename = time() . '_PKS_ADMIN_' . Str::slug($permohonan->kode) . '.pdf';
        $path = $file->storeAs('uploads/pks/' . $permohonan->id, $filename, 'public');

        PermohonanPks::create([
            'id_permohonan' => $permohonan->id,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'tipe' => PermohonanPks::TIPE_ADMIN,
            'status' => PermohonanPks::STATUS_APPROVED,
            'uploaded_by' => Auth::id(),
            'catatan' => $validated['catatan'] ?? null,
        ]);

        PermohonanHistori::create([
            'id_permohonan' => $permohonan->id,
            'id_operator' => Auth::id(),
            'role_operator' => Auth::user()->role_name,
            'deskripsi' => 'Admin mengupload PKS versi admin untuk pemohon.',
        ]);

        return redirect()->back()->with('success', 'PKS versi admin berhasil diupload.');
    }

    /**
     * Admin approve PKS final → status PELAKSANAAN.
     * Notifikasi: "Anda memasuki pelaksanaan kerjasama"
     */
    public function approve(Request $request, string $uuid)
    {
        $permohonan = Permohonan::uuid($uuid)->firstOrFail();

        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        // Pastikan dokumen TTD sudah divalidasi
        $hasValidatedTtd = $permohonan->ttdFiles()->where('is_validated', true)->exists();
        if (!$hasValidatedTtd) {
            return redirect()->back()->with('error', 'Dokumen pasca-tandatangan harus divalidasi terlebih dahulu.');
        }

        $hasAdminTtd = $permohonan->ttdFiles()
            ->where('tipe', \App\Models\PermohonanTtd::TIPE_ADMIN)
            ->exists();
        if (!$hasAdminTtd) {
            return redirect()->back()->with('error', 'Dokumen versi admin harus diupload sebelum PKS disetujui.');
        }

        $permohonan->update(['status' => Permohonan::STATUS_PELAKSANAAN]);

        // Approve all pending PKS
        $permohonan->pksFiles()->where('status', PermohonanPks::STATUS_PENDING)->update([
            'status' => PermohonanPks::STATUS_APPROVED,
        ]);

        PermohonanHistori::create([
            'id_permohonan' => $permohonan->id,
            'id_operator' => Auth::id(),
            'role_operator' => Auth::user()->role_name,
            'deskripsi' => 'PKS final disetujui. Memasuki tahap pelaksanaan kerjasama.',
        ]);

        // Notif ke pemohon: "Anda memasuki pelaksanaan kerjasama"
        $this->notifPemohonPelaksanaan($permohonan);

        return redirect()->back()->with('success', 'PKS final telah disetujui. Pemohon dinotifikasi memasuki pelaksanaan kerjasama.');
    }

    /**
     * Notif ke pemohon saat admin sudah upload PKS final
     * (status 3 → 4 / Menunggu Penandatanganan).
     */
    private function notifPemohonPksReady(Permohonan $permohonan): void
    {
        $user = \App\Models\User::find($permohonan->id_pemohon_0);
        $name = $permohonan->pemohon1?->name ?: ($user?->name ?: 'Pemohon');
        $payload = \App\Services\NotificationTemplate::pksFinalSiap($name, $permohonan);

        Notifikasi::create([
            'id_user'  => $permohonan->id_pemohon_0,
            'category' => 'permohonan',
            'title'    => $payload['system']['title'],
            'message'  => $payload['system']['message'],
            'data'     => json_encode(['uuid' => $permohonan->uuid]),
            'is_read'  => false,
        ]);

        try {
            $wa = app(WhatsappService::class);
            if ($user?->phone) {
                $wa->sendMessage($user->phone, $payload['wa']);
            }
        } catch (\Exception $e) {
            Log::error('Failed WA notif PKS ready: ' . $e->getMessage());
        }
    }

    /**
     * Notif WA ke admin group saat pemohon upload PKS
     * @deprecated Pemohon tidak lagi upload PKS final. Disimpan untuk back-compat.
     */
    private function notifAdminPksUploaded(Permohonan $permohonan): void
    {
        try {
            $wa = app(WhatsappService::class);
            $msg = \App\Services\NotificationTemplate::internalGroup(
                'PKS Final Diunggah',
                $permohonan,
                "PKS final telah diunggah dan siap untuk koordinasi waktu penandatanganan.",
                Auth::user()?->name
            );
            $wa->sendToGroup($msg);
        } catch (\Exception $e) {
            Log::error('Failed WA notif PKS upload: ' . $e->getMessage());
        }
    }

    /**
     * Notif ke pemohon saat memasuki pelaksanaan
     */
    private function notifPemohonPelaksanaan(Permohonan $permohonan): void
    {
        $user = \App\Models\User::find($permohonan->id_pemohon_0);
        $name = $permohonan->pemohon1?->name ?: ($user?->name ?: 'Pemohon');
        $payload = \App\Services\NotificationTemplate::pelaksanaanDimulai($name, $permohonan);

        Notifikasi::create([
            'id_user'  => $permohonan->id_pemohon_0,
            'category' => 'permohonan',
            'title'    => $payload['system']['title'],
            'message'  => $payload['system']['message'],
            'data'     => json_encode(['uuid' => $permohonan->uuid]),
            'is_read'  => false,
        ]);

        try {
            $wa = app(WhatsappService::class);
            if ($user?->phone) {
                $wa->sendMessage($user->phone, $payload['wa']);
            }
        } catch (\Exception $e) {
            Log::error('Failed WA notif pelaksanaan: ' . $e->getMessage());
        }
    }
}
