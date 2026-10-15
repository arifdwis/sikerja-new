<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ValidasiController extends Controller implements HasMiddleware
{
    protected $title;
    protected $prefix;
    protected $view;
    protected $share;

    public function __construct()
    {
        $this->title = 'Validasi Permohonan';
        $this->view = 'Backend/Validasi';
        $this->prefix = 'validasi';

        $this->share = [
            'title' => $this->title,
            'view' => $this->view,
            'prefix' => $this->prefix
        ];
    }

    public static function middleware(): array
    {
        return [
            new Middleware('can:permohonan.menu.validasi', only: ['index', 'update']),
        ];
    }

    public function index(Request $request)
    {
        $currentTab = $request->get('tab', 'all');
        $user = Auth::user();

        $baseQuery = Permohonan::where('is_draft', false);

        // Counts for tab badges
        $counts = [
            'all' => (clone $baseQuery)->whereIn('status', [
                Permohonan::STATUS_PERMOHONAN,
                Permohonan::STATUS_VERIFIKASI_ADMIN,
                Permohonan::STATUS_REVISI,
            ])->count(),
            'menunggu_staff' => (clone $baseQuery)->where('status', Permohonan::STATUS_PERMOHONAN)->count(),
            'menunggu_kabag' => (clone $baseQuery)->where('status', Permohonan::STATUS_VERIFIKASI_ADMIN)->count(),
            'revisi' => (clone $baseQuery)->where('status', Permohonan::STATUS_REVISI)->count(),
        ];

        $query = Permohonan::with(['kategori', 'pemohon1', 'pemohon2', 'operator.corporate', 'provinsi', 'kota', 'files'])
            ->where('is_draft', false);

        if ($currentTab === 'menunggu_staff') {
            $query->where('status', Permohonan::STATUS_PERMOHONAN);
        } elseif ($currentTab === 'menunggu_kabag') {
            $query->where('status', Permohonan::STATUS_VERIFIKASI_ADMIN);
        } elseif ($currentTab === 'revisi') {
            $query->where('status', Permohonan::STATUS_REVISI);
        } else {
            $query->whereIn('status', [
                Permohonan::STATUS_PERMOHONAN,
                Permohonan::STATUS_VERIFIKASI_ADMIN,
                Permohonan::STATUS_REVISI,
            ]);
        }

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('label', 'like', "%{$search}%")
                    ->orWhere('nomor_permohonan', 'like', "%{$search}%")
                    ->orWhere('nama_instansi', 'like', "%{$search}%")
                    ->orWhere('nama_pimpinan', 'like', "%{$search}%");
            });
        }

        $datas = $query->latest()->paginate(12)->withQueryString();

        return Inertia::render("$this->view/Index", [
            'datas' => $datas,
            'share' => $this->share,
            'counts' => $counts,
            'currentTab' => $currentTab,
            'isKabag' => $user->isKabagKerjasama() || $user->isAdmin(),
            'filters' => $request->only(['search', 'tab']),
        ]);
    }

    public function show(string $uuid)
    {
        $permohonan = Permohonan::with(['kategori', 'pemohon1', 'pemohon2', 'operator.corporate', 'provinsi', 'kota', 'files', 'historis.operator'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        return response()->json($permohonan);
    }

    public function update(Request $request, string $uuid)
    {
        $permohonan = Permohonan::where('uuid', $uuid)->firstOrFail();
        $user = Auth::user();

        $validated = $request->validate([
            'status' => 'required|in:1,9,10,11,99', // 10: Lolos Staf (ke Kabag), 1: Disposisi Kabag (ke Pembahasan), 11/99: Revisi, 9: Tolak
            'keterangan' => 'nullable|string|min:3',
        ]);

        $statusTarget = (int)$validated['status'];
        if ($statusTarget === 99) {
            $statusTarget = Permohonan::STATUS_REVISI; // 11
        }

        $keterangan = $validated['keterangan'] ?? null;

        if ($statusTarget === Permohonan::STATUS_VERIFIKASI_ADMIN) {
            // Verifikasi Administrasi Staff -> Diteruskan ke Kabag
            $permohonan->update([
                'status' => Permohonan::STATUS_VERIFIKASI_ADMIN,
                'alasan_tolak' => null,
            ]);

            \App\Models\PermohonanHistori::create([
                'id_permohonan' => $permohonan->id,
                'id_operator' => $user->id,
                'role_operator' => $user->role_name,
                'deskripsi' => 'Kelengkapan berkas diverifikasi lengkap oleh Staf Bagian Kerja Sama. Diteruskan ke Kepala Bagian Kerja Sama untuk validasi & disposisi.',
                'komentar' => $keterangan,
            ]);

            $message = 'Berkas berhasil diverifikasi dan diteruskan ke Kepala Bagian Kerja Sama.';
        } elseif ($statusTarget === Permohonan::STATUS_PEMBAHASAN) {
            // Validasi & Disposisi Kabag Kerja Sama -> Masuk Pembahasan TKKSD
            $permohonan->update([
                'status' => Permohonan::STATUS_PEMBAHASAN,
                'alasan_tolak' => null,
            ]);

            \App\Models\PermohonanHistori::create([
                'id_permohonan' => $permohonan->id,
                'id_operator' => $user->id,
                'role_operator' => $user->role_name,
                'deskripsi' => 'Disetujui oleh Kepala Bagian Kerja Sama. Permohonan dijadwalkan untuk pembahasan bersama TKKSD.',
                'komentar' => $keterangan ?? 'Disposisi: Lanjutkan ke pembahasan TKKSD.',
            ]);

            $message = 'Permohonan berhasil divalidasi dan masuk ke tahap Pembahasan TKKSD.';
        } elseif ($statusTarget === Permohonan::STATUS_REVISI) {
            // Kembalikan ke Pemohon untuk Revisi
            if (empty($keterangan)) {
                return redirect()->back()->withErrors(['keterangan' => 'Catatan revisi wajib diisi agar pemohon mengetahui perbaikan yang diperlukan.']);
            }

            $permohonan->update([
                'status' => Permohonan::STATUS_REVISI,
                'alasan_tolak' => $keterangan,
            ]);

            \App\Models\PermohonanHistori::create([
                'id_permohonan' => $permohonan->id,
                'id_operator' => $user->id,
                'role_operator' => $user->role_name,
                'deskripsi' => 'Permohonan dikembalikan ke pemohon untuk perbaikan berkas / kelengkapan.',
                'komentar' => $keterangan,
            ]);

            $message = 'Permohonan berhasil dikembalikan ke pemohon untuk revisi.';
        } elseif ($statusTarget === Permohonan::STATUS_DITOLAK) {
            // Tolak Resmi
            if (empty($keterangan)) {
                return redirect()->back()->withErrors(['keterangan' => 'Alasan penolakan wajib diisi.']);
            }

            $permohonan->update([
                'status' => Permohonan::STATUS_DITOLAK,
                'alasan_tolak' => $keterangan,
            ]);

            \App\Models\PermohonanHistori::create([
                'id_permohonan' => $permohonan->id,
                'id_operator' => $user->id,
                'role_operator' => $user->role_name,
                'deskripsi' => 'Permohonan kerja sama ditolak oleh Bagian Kerja Sama.',
                'komentar' => $keterangan,
            ]);

            $message = 'Permohonan kerja sama telah ditolak.';
        }

        return redirect()->back()->with('success', $message);
    }
}
