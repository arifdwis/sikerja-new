<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Laman;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Models\Permohonan;
use App\Models\User;

class LandingController extends Controller
{
    public function home()
    {
        $sliders = Slider::where('is_active', true)->latest()->get();
        if ($sliders->isEmpty()) {
            $sliders = collect([]);
        }

        $faqs = Faq::take(5)->get();

        $totalPermohonan = Permohonan::count();
        $permohonanSelesai = Permohonan::where('status', Permohonan::STATUS_SELESAI)->count();
        $permohonanAktif = Permohonan::whereNotIn('status', [
            Permohonan::STATUS_SELESAI,
            Permohonan::STATUS_DITOLAK,
        ])->count();

        $mitraCount = User::whereHas('roles', function ($query) {
            $query->where('name', '!=', 'Administrator');
        })->count();

        $stats = [
            'documents' => $totalPermohonan,
            'opds' => $mitraCount,
            'improvement' => $permohonanSelesai,
            'uptime' => $permohonanAktif
        ];

        return Inertia::render('Frontend/Landing/Home', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'sliders' => $sliders,
            'faqs' => $faqs,
            'stats' => $stats,
        ]);
    }

    public function about()
    {
        // Try to find a laman responsible for 'tentang' or 'about'
        // If not found, use the hardcoded view or partial content
        // For now, we will pass dynamic data to the existing view if available
        $page = Laman::where('slug', 'tentang-kami')->orWhere('slug', 'tentang-sikerja')->first();

        return Inertia::render('Frontend/Landing/About', [
            'page' => $page
        ]);
    }

    public function workflow()
    {
        // Static for now unless 'alur' laman exists
        return Inertia::render('Frontend/Landing/Workflow');
    }

    public function products()
    {
        // Static for now unless 'produk' laman exists
        return Inertia::render('Frontend/Landing/Products');
    }

    public function faq()
    {
        $faqs = Faq::all();
        return Inertia::render('Frontend/Landing/Faq', [
            'faqs' => $faqs
        ]);
    }

    /**
     * Halaman infografis publik (Req 15) — tanpa auth.
     */
    public function infografis(Request $request)
    {
        $tahun = $request->filled('tahun') ? (int) $request->integer('tahun') : null;
        $permohonanQuery = Permohonan::query();
        $manualQuery = \App\Models\KerjasamaManual::query();

        if ($tahun) {
            $permohonanQuery->whereYear('created_at', $tahun);
            $manualQuery->whereYear('created_at', $tahun);
        }

        // Stats dasar
        $total = (clone $permohonanQuery)->count();
        $aktif = (clone $permohonanQuery)->where('status', Permohonan::STATUS_PELAKSANAAN)->count();
        $selesai = (clone $permohonanQuery)->where('status', Permohonan::STATUS_SELESAI)->count();
        $dalamProses = (clone $permohonanQuery)->whereNotIn('status', [
            Permohonan::STATUS_PELAKSANAAN,
            Permohonan::STATUS_SELESAI,
            Permohonan::STATUS_DITOLAK,
        ])->count();

        // Tambah dari kerjasama_manual (Req 17.5)
        $manualTotal = (clone $manualQuery)->count();
        $manualAktif = (clone $manualQuery)->whereNotNull('tanggal_berakhir')
            ->where('tanggal_berakhir', '>=', now())
            ->count();
        $manualSelesai = (clone $manualQuery)->whereNotNull('tanggal_berakhir')
            ->where('tanggal_berakhir', '<', now())
            ->count();

        $stats = [
            'total' => $total + $manualTotal,
            'aktif' => $aktif + $manualAktif,
            'selesai' => $selesai + $manualSelesai,
            'dalam_proses' => $dalamProses,
        ];

        // Distribusi per kategori
        $kategoriPermohonan = (clone $permohonanQuery)->with('kategori')
            ->selectRaw('id_kategori, COUNT(*) as total')
            ->groupBy('id_kategori')
            ->get()
            ->map(fn($p) => [
                'kategori' => $p->kategori?->label ?? 'Lainnya',
                'total' => $p->total,
            ]);

        $kategoriManual = (clone $manualQuery)->with('kategori')
            ->selectRaw('id_kategori, COUNT(*) as total')
            ->groupBy('id_kategori')
            ->get()
            ->map(fn($p) => [
                'kategori' => $p->kategori?->label ?? 'Lainnya',
                'total' => $p->total,
            ]);

        $perKategori = $kategoriPermohonan->merge($kategoriManual)
            ->groupBy('kategori')
            ->map(fn($items, $kategori) => [
                'kategori' => $kategori,
                'total' => $items->sum('total'),
            ])
            ->sortByDesc('total')
            ->values();

        // Top OPD pelaksana (gabungan dari permohonan & kerjasama_manual)
        $perOpd = \App\Models\Opd::withCount([
            'permohonans' => fn($query) => $tahun ? $query->whereYear('permohonan.created_at', $tahun) : $query,
            'kerjasamaManuals' => fn($query) => $tahun ? $query->whereYear('kerjasama_manual.created_at', $tahun) : $query,
        ])
            ->get()
            ->map(fn($o) => [
                'opd' => $o->singkatan ?: $o->nama,
                'total' => $o->permohonans_count + $o->kerjasama_manuals_count,
            ])
            ->filter(fn($r) => $r['total'] > 0)
            ->sortByDesc('total')
            ->take(9)
            ->values();

        $trendPermohonan = Permohonan::selectRaw('YEAR(created_at) as tahun, COUNT(*) as total')
            ->groupBy('tahun')
            ->get();
        $trendManual = \App\Models\KerjasamaManual::selectRaw('YEAR(created_at) as tahun, COUNT(*) as total')
            ->groupBy('tahun')
            ->get();

        // Trend tahunan tetap global agar filter tahun punya konteks pembanding.
        $trendTahun = $trendPermohonan->merge($trendManual)
            ->groupBy('tahun')
            ->map(fn($items, $year) => [
                'tahun' => (int) $year,
                'total' => $items->sum('total'),
            ])
            ->sortByDesc('tahun')
            ->take(5)
            ->reverse()
            ->values();

        $bulanLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];
        $bulananPermohonan = (clone $permohonanQuery)
            ->selectRaw('MONTH(created_at) as bulan, COUNT(*) as total')
            ->groupBy('bulan')
            ->pluck('total', 'bulan');
        $bulananManual = (clone $manualQuery)
            ->selectRaw('MONTH(created_at) as bulan, COUNT(*) as total')
            ->groupBy('bulan')
            ->pluck('total', 'bulan');

        $trendBulanan = collect(range(1, 12))->map(fn($bulan) => [
            'bulan' => $bulanLabels[$bulan - 1],
            'total' => ($bulananPermohonan[$bulan] ?? 0) + ($bulananManual[$bulan] ?? 0),
        ]);

        $statusCounts = (clone $permohonanQuery)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
        $perStatus = collect(Permohonan::statusLabels())
            ->map(fn($status, $code) => [
                'status' => $status['label'],
                'total' => $statusCounts[$code] ?? 0,
            ])
            ->filter(fn($status) => $status['total'] > 0)
            ->values()
            ->push(['status' => 'Data Manual Aktif', 'total' => $manualAktif])
            ->push(['status' => 'Data Manual Berakhir', 'total' => $manualSelesai])
            ->filter(fn($status) => $status['total'] > 0)
            ->values();

        // Top 5 instansi mitra
        $instansiPermohonan = (clone $permohonanQuery)->selectRaw('nama_instansi, COUNT(*) as total')
            ->whereNotNull('nama_instansi')
            ->groupBy('nama_instansi')
            ->get();
        $instansiManual = (clone $manualQuery)->selectRaw('nama_instansi, COUNT(*) as total')
            ->whereNotNull('nama_instansi')
            ->groupBy('nama_instansi')
            ->get();
        $topInstansi = $instansiPermohonan->merge($instansiManual)
            ->groupBy('nama_instansi')
            ->map(fn($items, $instansi) => [
                'nama_instansi' => $instansi,
                'total' => $items->sum('total'),
            ])
            ->sortByDesc('total')
            ->take(5)
            ->values();

        $availableYears = $trendPermohonan->pluck('tahun')
            ->merge($trendManual->pluck('tahun'))
            ->filter()
            ->map(fn($year) => (int) $year)
            ->unique()
            ->sortDesc()
            ->values();

        return Inertia::render('Frontend/Landing/Infografis', [
            'stats' => $stats,
            'perKategori' => $perKategori,
            'perOpd' => $perOpd,
            'trendTahun' => $trendTahun,
            'trendBulanan' => $trendBulanan,
            'perStatus' => $perStatus,
            'topInstansi' => $topInstansi,
            'availableYears' => $availableYears,
            'tahun' => $tahun,
        ]);
    }

    public function page($slug)
    {
        $page = Laman::where('slug', $slug)->firstOrFail();

        $fileLinks = [];
        if ($page->content) {
            preg_match_all('/<a\s+[^>]*href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/si', $page->content, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                $fileLinks[] = [
                    'href' => $match[1],
                    'text' => strip_tags($match[2]),
                ];
            }
        }

        return Inertia::render('Frontend/Landing/Page', [
            'page' => $page,
            'fileLinks' => $fileLinks,
        ]);
    }
}
