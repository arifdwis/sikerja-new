<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use App\Models\Penjadwalan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class PenjadwalanController extends Controller implements HasMiddleware
{
    protected $title;
    protected $prefix;
    protected $view;
    protected $share;

    public function __construct()
    {
        $this->title = 'Penjadwalan';
        $this->view = 'Backend/Penjadwalan';
        $this->prefix = 'penjadwalan';

        $this->share = [
            'title' => $this->title,
            'view' => $this->view,
            'prefix' => $this->prefix
        ];
    }

    public static function middleware(): array
    {
        return [
            new Middleware('can:penjadwalan.index', only: ['index']),
            // Custom authorization for store to allow pemohon
            // new Middleware('can:penjadwalan.create', only: ['create', 'store']),
            new Middleware('can:penjadwalan.edit', only: ['edit', 'update']),
        ];
    }

    public function index(Request $request)
    {
        $query = Penjadwalan::with(['permohonan', 'creator', 'approver'])
            ->latest('tanggal');

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('agenda', 'like', "%{$search}%")
                    ->orWhereHas('permohonan', function ($q2) use ($search) {
                        $q2->where('nama_instansi', 'like', "%{$search}%")
                            ->orWhere('nomor_permohonan', 'like', "%{$search}%");
                    });
            });
        }

        $datas = $query->paginate(10)->withQueryString();

        return Inertia::render("$this->view/Index", [
            'datas' => $datas,
            'share' => $this->share,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Store new schedule. 
     * Can be called by Pemohon or Admin.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_permohonan' => 'required|exists:permohonan,id',
            // Req 6: Metode penjadwalan baru
            'tipe' => 'required|in:desk_to_desk,seremonial,hybrid',
            'tanggal' => 'required|date',
            'waktu' => 'required',
            'lokasi' => 'nullable|string',
            'agenda' => 'required|string',
            'keterangan' => 'nullable|string',
        ], [
            'tipe.in' => 'Metode penjadwalan harus salah satu: Desk to Desk, Seremonial, atau Hybrid.',
        ]);

        $permohonan = Permohonan::findOrFail($request->id_permohonan);
        $user = $request->user();

        // Check permission if user is pemohon
        if (!$user->can('penjadwalan.create')) {
            if ($permohonan->id_pemohon_0 != $user->id) {
                abort(403, 'Anda tidak memiliki hak akses untuk membuat jadwal permohonan ini.');
            }
        }

        // Check for existing pending or approved schedule
        $existingJadwal = Penjadwalan::where('id_permohonan', $permohonan->id)
            ->whereIn('status', [0, 1])
            ->exists();

        if ($existingJadwal) {
            return back()->with('error', 'Jadwal sudah diajukan dan sedang diproses atau sudah disetujui.');
        }

        // Create Histori
        $histori = \App\Models\PermohonanHistori::create([
            'id_permohonan' => $permohonan->id,
            'id_operator' => $user->id,
            'role_operator' => $user->role_name,
            'deskripsi' => 'Mengajukan jadwal penandatanganan kerjasama.',
        ]);

        // Create Penjadwalan
        $jadwal = Penjadwalan::create([
            'id_permohonan' => $permohonan->id,
            'id_histori' => $histori->id,
            'tipe' => $validated['tipe'],
            'tanggal' => $validated['tanggal'],
            'waktu' => $validated['waktu'],
            'lokasi' => $validated['lokasi'] ?? null,
            'agenda' => $validated['agenda'],
            'keterangan' => $validated['keterangan'] ?? null,
            'status' => 0, // Menunggu Persetujuan
            'created_by' => $user->id,
        ]);

        // Send notification to Admin/TKKSD — admin pakai whitelist via scope, TKKSD apa adanya
        $adminIds = \App\Models\User::adminNotificationRecipients()->pluck('id');
        $tkksdIds = \App\Models\User::whereHas('roles', fn($q) => $q->where('slug', 'tkksd'))->pluck('id');
        $adminIds = $adminIds->merge($tkksdIds)->unique();

        $tanggalJadwal = \Carbon\Carbon::parse($validated['tanggal'])->translatedFormat('l, d F Y');
        $waJadwal = \App\Services\NotificationTemplate::jadwalDiajukan($permohonan, "{$tanggalJadwal} pukul {$validated['waktu']} WITA", $user->name);

        foreach ($adminIds as $adminId) {
            \App\Models\Notifikasi::create([
                'id_user'       => $adminId,
                'id_permohonan' => $permohonan->id,
                'from_user_id'  => $user->id,
                'type'          => 'penjadwalan',
                'title'         => 'Pengajuan Jadwal Penandatanganan',
                'message'       => "Pemohon mengajukan jadwal penandatanganan untuk \"{$permohonan->label}\" pada {$tanggalJadwal} pukul {$validated['waktu']} WITA. Mohon meninjau dan memberikan persetujuan.",
            ]);
        }

        // WA grup admin
        try { app(\App\Services\WhatsappService::class)->sendToGroup($waJadwal); } catch (\Throwable $e) {}

        return back()->with('success', 'Jadwal penandatanganan berhasil diajukan, menunggu persetujuan Admin.');
    }

    /**
     * Review schedule (Approve/Reject) by Admin
     */
    public function review(Request $request, string $uuid)
    {
        // Check permission (Admin only)
        // if (!$request->user()->can('penjadwalan.edit')) {
        //     abort(403);
        // }

        $request->validate([
            'status' => 'required|in:1,2', // 1: Approve, 2: Reject
            'admin_comment' => 'nullable|string',
        ]);

        $jadwal = Penjadwalan::where('uuid', $uuid)->firstOrFail();
        $user = $request->user();

        $jadwal->update([
            'status' => $request->status,
            'approved_by' => $user->id,
            'approved_at' => now(),
            'admin_comment' => $request->admin_comment,
        ]);

        $permohonan = $jadwal->permohonan;

        if ($request->status == 1) {
            // Approved → Status JADWAL_DISETUJUI (3) — Pemohon harus upload PKS
            $permohonan->update([
                'status' => Permohonan::STATUS_JADWAL_DISETUJUI,
                'status_selesai' => 'PROSES',
            ]);
            $title = 'Jadwal Penandatanganan Disetujui';
            // Req 8: Notifikasi baru
            $message = "Jadwal penandatanganan untuk permohonan {$permohonan->kode} disetujui pada " . \Carbon\Carbon::parse($jadwal->tanggal)->translatedFormat('d F Y') . ". Menunggu waktu penandatanganan, harap mempersiapkan seluruh dokumen yang akan ditandatangani.";

            // Histori
            \App\Models\PermohonanHistori::create([
                'id_permohonan' => $permohonan->id,
                'id_operator' => $user->id,
                'role_operator' => $user->role_name,
                'deskripsi' => 'Jadwal penandatanganan disetujui. Pemohon dapat mengupload PKS final.',
            ]);

            // WA notif ke pemohon
            $this->notifPemohonJadwalDisetujui($permohonan, $jadwal);
        } else {
            // Rejected
            $title = 'Jadwal Ditolak';
            $message = "Jadwal penandatanganan untuk permohonan {$permohonan->kode} ditolak. " . ($request->admin_comment ? "Alasan: {$request->admin_comment}" : '');
        }

        // Notify Pemohon
        \App\Models\Notifikasi::create([
            'id_user' => $permohonan->id_pemohon_0,
            'id_permohonan' => $permohonan->id,
            'from_user_id' => $user->id,
            'type' => 'penjadwalan_review',
            'title' => $title,
            'message' => $message,
        ]);

        return back()->with('success', "Jadwal berhasil " . ($request->status == 1 ? 'disetujui' : 'ditolak'));
    }

    public function destroy(string $uuid)
    {
        $penjadwalan = Penjadwalan::where('uuid', $uuid)->firstOrFail();
        $penjadwalan->delete();

        return redirect()->route("$this->prefix.index")
            ->with('success', 'Jadwal berhasil dihapus.');
    }

    /**
     * Kirim WA notif ke pemohon saat jadwal disetujui (Req 8) — pakai template formal.
     */
    private function notifPemohonJadwalDisetujui(Permohonan $permohonan, Penjadwalan $jadwal): void
    {
        try {
            $wa = app(\App\Services\WhatsappService::class);
            $user = \App\Models\User::find($permohonan->id_pemohon_0);
            $phone = $user?->phone ?: ($permohonan->pemohon1?->phone ?? null);

            $name = $permohonan->pemohon1?->name ?: ($user?->name ?: 'Pemohon');
            $tglFormat = \Carbon\Carbon::parse($jadwal->tanggal)->translatedFormat('l, d F Y');
            $tipeLabel = match ($jadwal->tipe) {
                'desk_to_desk' => 'Desk to Desk',
                'seremonial'   => 'Seremonial',
                'hybrid'       => 'Hybrid',
                default        => $jadwal->tipe,
            };
            $tanggalJadwalLengkap = "{$tglFormat} pukul {$jadwal->waktu} WITA"
                . ($jadwal->lokasi ? " — Lokasi: {$jadwal->lokasi}" : '')
                . " (Metode: {$tipeLabel})";

            $payload = \App\Services\NotificationTemplate::jadwalDisetujui($name, $permohonan, $tanggalJadwalLengkap);
            if ($phone) {
                $wa->sendMessage($phone, $payload['wa']);
            }
        } catch (\Exception $e) {
            \Log::error('Failed WA notif jadwal: ' . $e->getMessage());
        }
    }
}
