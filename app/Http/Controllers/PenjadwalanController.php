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
            'tipe' => 'required|in:calendar,langsung',
            'tanggal' => 'required|date',
            'waktu' => 'required',
            'lokasi' => 'required_if:tipe,langsung',
            'agenda' => 'required|string',
            'keterangan' => 'nullable|string',
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
            'deskripsi' => 'Mengajukan jadwal pembahasan.',
        ]);

        // Create Penjadwalan
        $jadwal = Penjadwalan::create([
            'id_permohonan' => $permohonan->id,
            'id_histori' => $histori->id,
            'tipe' => $request->tipe,
            'tanggal' => $request->tanggal,
            'waktu' => $request->waktu,
            'lokasi' => $request->tipe == 'calendar' ? 'Online/Calendar' : $request->lokasi,
            'agenda' => $request->agenda,
            'keterangan' => $request->keterangan,
            'status' => 0, // Menunggu Persetujuan
            'created_by' => $user->id,
        ]);

        // Send notification to Admin/TKKSD
        $adminIds = \App\Models\User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['admin', 'tkksd', 'verifikator']);
        })->pluck('id');

        foreach ($adminIds as $adminId) {
            \App\Models\Notifikasi::create([
                'id_user' => $adminId,
                'id_permohonan' => $permohonan->id,
                'from_user_id' => $user->id,
                'type' => 'penjadwalan',
                'title' => 'Pengajuan Jadwal Baru',
                'message' => "Jadwal baru diajukan untuk permohonan {$permohonan->kode} pada tanggal " . \Carbon\Carbon::parse($request->tanggal)->translatedFormat('d F Y'),
            ]);
        }

        return back()->with('success', 'Jadwal berhasil diajukan, menunggu persetujuan Admin.');
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
            // Approved
            $permohonan->update([
                'status' => Permohonan::STATUS_DISETUJUI, // Next step (or Selesai?)
                'status_selesai' => 'PROSES',
            ]);
            $title = 'Jadwal Disetujui';
            $message = "Jadwal anda untuk permohonan {$permohonan->kode} tanggal " . \Carbon\Carbon::parse($jadwal->tanggal)->translatedFormat('d F Y') . " telah disetujui.";
        } else {
            // Rejected
            // Status remains Penjadwalan so they can resubmit?
            // Or maybe stay in Penjadwalan (2)
            $title = 'Jadwal Ditolak';
            $message = "Jadwal anda untuk permohonan {$permohonan->kode} ditolak. " . ($request->admin_comment ? "Alasan: {$request->admin_comment}" : '');
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
}
