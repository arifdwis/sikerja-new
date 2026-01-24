<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class PersetujuanController extends Controller implements HasMiddleware
{
    protected $title;
    protected $prefix;
    protected $view;
    protected $share;

    public function __construct()
    {
        $this->title = 'Persetujuan Permohonan';
        $this->view = 'Backend/Persetujuan';
        $this->prefix = 'persetujuan';

        $this->share = [
            'title' => $this->title,
            'view' => $this->view,
            'prefix' => $this->prefix
        ];
    }

    public static function middleware(): array
    {
        return [
            new Middleware('can:permohonan.menu.persetujuan', only: ['index', 'update']),
        ];
    }

    public function index(Request $request)
    {
        $query = Permohonan::with([
            'kategori',
            'pemohon1',
            'operator.corporate',
            'provinsi',
            'kota',
            'penjadwalans' => function ($q) {
                $q->latest();
            }
        ])
            ->where('status', Permohonan::STATUS_PENJADWALAN) // Status 2: Menunggu Persetujuan Jadwal
            ->latest();

        if ($request->has('kategori') && $request->kategori) {
            $query->where('id_kategori', $request->kategori);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('label', 'like', "%{$search}%")
                    ->orWhere('nomor_permohonan', 'like', "%{$search}%")
                    ->orWhere('nama_instansi', 'like', "%{$search}%");
            });
        }

        $datas = $query->paginate(10)->withQueryString();
        $kategoris = \App\Models\Kategori::all();

        return Inertia::render("$this->view/Index", [
            'datas' => $datas,
            'kategoris' => $kategoris,
            'share' => $this->share,
            'filters' => $request->only(['search', 'kategori']),
        ]);
    }

    public function show(string $uuid)
    {
        $permohonan = Permohonan::with(['kategori', 'pemohon1', 'operator.corporate', 'provinsi', 'kota', 'files', 'penjadwalans'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        return response()->json($permohonan);
    }

    public function update(Request $request, string $uuid)
    {
        $permohonan = Permohonan::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'status' => 'required|in:1,9', // 1: Approve, 9: Reject
            'admin_comment' => 'nullable|string',
        ]);

        // Find pending schedule
        $pendingJadwal = $permohonan->penjadwalans()->where('status', 0)->latest()->first();

        if ($validated['status'] == 9) {
            // REJECT
            if ($pendingJadwal) {
                $pendingJadwal->update([
                    'status' => 2, // Ditolak
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                    'admin_comment' => $validated['admin_comment'] ?? 'Ditolak melalui menu Persetujuan',
                ]);
                return redirect()->back()->with('success', 'Jadwal ditolak. Menunggu jadwal baru.');
            }

            return redirect()->back()->with('error', 'Tidak ada jadwal yang perlu ditolak.');

        } else {
            // APPROVE
            if ($pendingJadwal) {
                $pendingJadwal->update([
                    'status' => 1, // Disetujui
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                    'admin_comment' => $validated['admin_comment'] ?? 'Disetujui melalui menu Persetujuan',
                ]);

                $permohonan->update([
                    'status' => Permohonan::STATUS_SELESAI, // Status 4: Selesai
                ]);

                return redirect()->back()->with('success', 'Jadwal disetujui. Permohonan kerjasama selesai.');
            }

            return redirect()->back()->with('error', 'Tidak ada jadwal yang perlu disetujui.');
        }
    }
}
