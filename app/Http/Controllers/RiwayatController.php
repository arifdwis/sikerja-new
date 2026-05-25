<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class RiwayatController extends Controller implements HasMiddleware
{
    protected $title;
    protected $prefix;
    protected $view;
    protected $share;

    public function __construct()
    {
        $this->title = 'Riwayat Permohonan';
        $this->view = 'Backend/Riwayat';
        $this->prefix = 'riwayat';

        $this->share = [
            'title' => $this->title,
            'view' => $this->view,
            'prefix' => $this->prefix
        ];
    }

    public static function middleware(): array
    {
        return [
            new Middleware('can:riwayat.index', only: ['index']),
        ];
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Permohonan::with(['kategori', 'pemohon1', 'operator', 'provinsi', 'kota']);

        // Riwayat berisi semua permohonan yang sudah "tidak aktif lagi di alur":
        // - 6 = Pelaksanaan Kerjasama (sudah dieksekusi, sedang berjalan)
        // - 7 = Selesai (kerjasama sudah selesai dengan monev final)
        // - 9 = Ditolak
        $finalStatuses = [
            Permohonan::STATUS_PELAKSANAAN,
            Permohonan::STATUS_SELESAI,
            Permohonan::STATUS_DITOLAK,
        ];

        // Jika pemohon, hanya tampilkan miliknya
        if ($user->hasRole('pemohon')) {
            $query->where('id_pemohon_0', $user->id);
        }
        $query->whereIn('status', $finalStatuses);

        $query->latest();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('label', 'like', "%{$search}%")
                    ->orWhere('nomor_permohonan', 'like', "%{$search}%")
                    ->orWhere('nama_instansi', 'like', "%{$search}%");
            });
        }

        $datas = $query->paginate(10)->withQueryString();

        return Inertia::render("$this->view/Index", [
            'datas' => $datas,
            'share' => $this->share,
            'filters' => $request->only(['search']),
        ]);
    }
}
