<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Routing per role: setiap peran punya dashboard sendiri sesuai kebutuhan kerjanya.
        if ($user->hasRole('pemohon') && !$user->hasRole(['administrator', 'superadmin'])) {
            return $this->dashboardPemohon($request);
        }

        if ($user->hasRole('tkksd_lokus') && !$user->hasRole(['administrator', 'superadmin'])) {
            return $this->dashboardTkksdLokus($request);
        }

        if ($user->hasRole('tkksd') && !$user->hasRole(['administrator', 'superadmin'])) {
            return $this->dashboardTkksd($request);
        }

        return $this->dashboardAdmin($request);
    }

    /**
     * Dashboard untuk Admin / Superadmin / Administrator.
     * Memuat statistik global, chart trend, kategori, lokasi, top instansi, dll.
     */
    protected function dashboardAdmin(Request $request)
    {
        $tahun = $request->get('tahun', date('Y'));
        $kategoriId = $request->get('kategori_id');
        $bulan = $request->get('bulan');

        $user = $request->user();

        $query = Permohonan::whereYear('created_at', $tahun);

        if ($kategoriId) {
            $query->where('id_kategori', $kategoriId);
        }

        if ($bulan) {
            $query->whereMonth('created_at', $bulan);
        }

        $statusCounts = (clone $query)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $stats = [
            'total' => $statusCounts->sum(),
            'permohonan' => $statusCounts[Permohonan::STATUS_PERMOHONAN] ?? 0,
            'pembahasan' => $statusCounts[Permohonan::STATUS_PEMBAHASAN] ?? 0,
            'penjadwalan' => $statusCounts[Permohonan::STATUS_PENJADWALAN] ?? 0,
            'disetujui' => $statusCounts[Permohonan::STATUS_PELAKSANAAN] ?? 0,
            'selesai' => $statusCounts[Permohonan::STATUS_SELESAI] ?? 0,
            'dicabut' => $statusCounts[Permohonan::STATUS_DICABUT] ?? 0,
            'ditolak' => $statusCounts[Permohonan::STATUS_DITOLAK] ?? 0,
        ];

        // Chart data: Trend per bulan (Current Year)
        $trendCurrent = (clone $query)
            ->selectRaw('MONTH(created_at) as bulan, COUNT(*) as total')
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->pluck('total', 'bulan')
            ->toArray();

        // Chart data: Trend per bulan (Last Year)
        // Chart data: Trend per bulan (Last Year)
        $trendLastYear = Permohonan::whereYear('created_at', $tahun - 1);

        $trendLastYear = $trendLastYear
            ->selectRaw('MONTH(created_at) as bulan, COUNT(*) as total')
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->pluck('total', 'bulan')
            ->toArray();

        $chartTrend = []; // Combo for "Trend Chart" (single or comparison)
        $chartComparison = []; // Specific comparison structure

        $bulanLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];

        $dataCurrent = [];
        $dataLast = [];

        for ($i = 1; $i <= 12; $i++) {
            $valCurrent = $trendCurrent[$i] ?? 0;
            $valLast = $trendLastYear[$i] ?? 0;

            // Legacy structure for TrendChart
            $chartTrend[] = [
                'bulan' => $bulanLabels[$i - 1],
                'total' => $valCurrent,
            ];

            $dataCurrent[] = $valCurrent;
            $dataLast[] = $valLast;
        }

        $chartComparison = [
            'labels' => $bulanLabels,
            'datasets' => [
                [
                    'label' => 'Tahun ' . $tahun,
                    'data' => $dataCurrent,
                    'color' => 'teal' // Frontend to map
                ],
                [
                    'label' => 'Tahun ' . ($tahun - 1),
                    'data' => $dataLast,
                    'color' => 'gray' // Frontend to map
                ]
            ]
        ];

        // Chart data: Per kategori
        // For Pemohon, we want to know THEIR distribution.
        // If Admin, all distribution.
        // Helper closure for category counting
        $getCategoryCounts = function ($query) {
            return Kategori::withCount([
                'permohonans' => function ($q) use ($query) {
                    $q->mergeConstraintsFrom($query);
                }
            ])
                ->get()
                ->map(function ($item) {
                    return ['kategori' => $item->label, 'total' => $item->permohonans_count];
                })
                ->sortByDesc('total')
                ->values();
        };

        $baseCatQuery = Permohonan::whereYear('created_at', $tahun);

        $allCategories = $getCategoryCounts($baseCatQuery);

        // Take Top 5 and Group Others
        $chartKategori = $allCategories->take(5);
        $othersCount = $allCategories->slice(5)->sum('total');

        if ($othersCount > 0) {
            $chartKategori->push(['kategori' => 'Lainnya', 'total' => $othersCount]);
        }

        $chartKategori = $chartKategori->values();


        // Chart data: Per status (For Admin mainly, but we pass structure anyway)
        $chartStatus = collect(Permohonan::statusLabels())->map(function ($item, $status) use ($statusCounts) {
            return [
                'status' => $item['label'],
                'color' => $item['color'],
                'total' => $statusCounts[$status] ?? 0,
            ];
        })->values();

        // NEW CHART: Lokasi (Kota)
        $chartLokasi = (clone $query)
            ->join('master_cities', 'permohonan.id_kota', '=', 'master_cities.id')
            ->select('master_cities.name as kota', DB::raw('count(*) as total'))
            ->groupBy('master_cities.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return ['kota' => $item->kota, 'total' => $item->total];
            });

        // NEW CHART: Stacked Status per Month
        // We use base query logic but grouped by Month and Status
        // Note: For trend/comparison we used year-based query. For this stacked chart, let's also use year-based query to show full year unless month filter is set?
        // If month filter is set, it will show 1 bar. That's fine.
        $stackedQuery = (clone $query); // Use base query with filters

        $stackedDataRaw = $stackedQuery
            ->selectRaw('MONTH(created_at) as bulan, status, count(*) as total')
            ->groupBy('bulan', 'status')
            ->get();

        $chartStacked = [
            'labels' => $bulanLabels,
            'datasets' => []
        ];

        $statuses = Permohonan::statusLabels();
        foreach ($statuses as $code => $labelInfo) {
            $dataSeries = [];
            for ($m = 1; $m <= 12; $m++) {
                $val = $stackedDataRaw->where('bulan', $m)->where('status', $code)->first();
                $dataSeries[] = $val ? $val->total : 0;
            }
            $chartStacked['datasets'][] = [
                'label' => $labelInfo['label'],
                'data' => $dataSeries,
                'color' => $labelInfo['color']
            ];
        }

        // Permohonan terbaru
        $permohonanTerbaru = (clone $query)
            ->with(['kategori', 'operator'])
            ->latest()
            ->limit(10)
            ->get();

        // Permohonan yang butuh tindakan (pending/pembahasan)
        $needsAction = (clone $query)
            ->with(['kategori', 'operator'])
            ->whereIn('status', [Permohonan::STATUS_PERMOHONAN, Permohonan::STATUS_PEMBAHASAN])
            ->latest()
            ->limit(5)
            ->get();

        // Top 5 Instansi
        $topAgencies = (clone $query)
            ->select('nama_instansi', DB::raw('count(*) as total'))
            ->groupBy('nama_instansi')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Tahun tersedia
        // If pemohon, only their years? Maybe just all years is fine.
        $availableYears = Permohonan::selectRaw('YEAR(created_at) as tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun');

        if ($availableYears->isEmpty()) {
            $availableYears = collect([date('Y')]);
        }

        $filterCategories = Kategori::select('id', 'label')->orderBy('label')->get();

        return Inertia::render('Backend/Dashboard', [
            'stats' => $stats,
            'chartTrend' => $chartTrend,
            'chartComparison' => $chartComparison,
            'chartKategori' => $chartKategori,
            'chartStatus' => $chartStatus,
            'chartLokasi' => $chartLokasi,
            'chartStacked' => $chartStacked,
            'permohonanTerbaru' => $permohonanTerbaru,
            'needsAction' => $needsAction,
            'topAgencies' => $topAgencies,
            'tahun' => (int) $tahun,
            'kategoriId' => $kategoriId,
            'bulan' => $bulan,
            'availableYears' => $availableYears,
            'filterCategories' => $filterCategories,
        ]);
    }

    /**
     * Dashboard untuk Pemohon — hanya data miliknya sendiri.
     */
    protected function dashboardPemohon(Request $request)
    {
        $user = $request->user();
        $tahun = $request->get('tahun', date('Y'));

        $query = Permohonan::where('id_pemohon_0', $user->id)
            ->whereYear('created_at', $tahun);

        $statusCounts = (clone $query)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $stats = [
            'total' => $statusCounts->sum(),
            'permohonan' => $statusCounts[Permohonan::STATUS_PERMOHONAN] ?? 0,
            'pembahasan' => $statusCounts[Permohonan::STATUS_PEMBAHASAN] ?? 0,
            'penjadwalan' => $statusCounts[Permohonan::STATUS_PENJADWALAN] ?? 0,
            'pelaksanaan' => $statusCounts[Permohonan::STATUS_PELAKSANAAN] ?? 0,
            'selesai' => $statusCounts[Permohonan::STATUS_SELESAI] ?? 0,
            'dicabut' => $statusCounts[Permohonan::STATUS_DICABUT] ?? 0,
            'ditolak' => $statusCounts[Permohonan::STATUS_DITOLAK] ?? 0,
        ];

        $bulanLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];
        $trendCurrent = (clone $query)
            ->selectRaw('MONTH(created_at) as bulan, COUNT(*) as total')
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->pluck('total', 'bulan')
            ->toArray();
        $chartTrend = [];
        for ($i = 1; $i <= 12; $i++) {
            $chartTrend[] = ['bulan' => $bulanLabels[$i - 1], 'total' => $trendCurrent[$i] ?? 0];
        }

        $chartStatus = collect(Permohonan::statusLabels())->map(fn($info, $code) => [
            'status' => $info['label'],
            'color' => $info['color'],
            'total' => $statusCounts[$code] ?? 0,
        ])->values();

        $permohonanTerbaru = (clone $query)
            ->with(['kategori', 'operator'])
            ->latest()
            ->limit(10)
            ->get();

        // Alerts kerjasama yang akan/sudah berakhir
        $alerts = [];
        $expiringSoon = Permohonan::where('id_pemohon_0', $user->id)
            ->whereIn('status', [Permohonan::STATUS_PELAKSANAAN, Permohonan::STATUS_SELESAI])
            ->whereNotNull('tanggal_berakhir')
            ->where('tanggal_berakhir', '>', now())
            ->where('tanggal_berakhir', '<=', now()->addDays(30))
            ->get();
        foreach ($expiringSoon as $item) {
            $daysLeft = now()->diffInDays(\Carbon\Carbon::parse($item->tanggal_berakhir));
            $alerts[] = [
                'type' => 'warning',
                'message' => "Kerjasama '{$item->label}' akan berakhir dalam {$daysLeft} hari ({$item->tanggal_berakhir}).",
                'link' => route('permohonan.show', $item->uuid),
            ];
        }

        $expired = Permohonan::where('id_pemohon_0', $user->id)
            ->whereIn('status', [Permohonan::STATUS_PELAKSANAAN, Permohonan::STATUS_SELESAI])
            ->whereNotNull('tanggal_berakhir')
            ->where('tanggal_berakhir', '<=', now())
            ->whereDoesntHave('monev')
            ->limit(5)
            ->get();
        foreach ($expired as $item) {
            $alerts[] = [
                'type' => 'error',
                'message' => "Kerjasama '{$item->label}' telah berakhir, silakan lakukan Monev.",
                'link' => route('monev.index'),
            ];
        }

        return Inertia::render('Backend/DashboardPemohon', [
            'stats' => $stats,
            'chartTrend' => $chartTrend,
            'chartStatus' => $chartStatus,
            'permohonanTerbaru' => $permohonanTerbaru,
            'kategoris' => Kategori::all(),
            'provinsis' => \App\Models\Provinsi::all(),
            'pemohon' => $user->pemohon,
            'corporate' => $user->corporate,
            'pemohonanList' => Permohonan::where('id_pemohon_0', $user->id)
                ->orWhere('id_pemohon_1', $user->id)
                ->get(),
            'alerts' => $alerts,
        ]);
    }

    /**
     * Dashboard untuk TKKSD (validator/pembahas).
     * Fokus: antrean validasi & pembahasan yang menunggu tindakannya.
     */
    protected function dashboardTkksd(Request $request)
    {
        $user = $request->user();
        $tahun = $request->get('tahun', date('Y'));

        $base = Permohonan::whereYear('created_at', $tahun);

        $stats = [
            'menunggu_validasi' => (clone $base)->where('status', Permohonan::STATUS_PERMOHONAN)->count(),
            'pembahasan' => (clone $base)->where('status', Permohonan::STATUS_PEMBAHASAN)->count(),
            'persetujuan_jadwal' => (clone $base)->where('status', Permohonan::STATUS_PENJADWALAN)->count(),
            'penandatanganan' => (clone $base)->whereIn('status', [
                Permohonan::STATUS_JADWAL_DISETUJUI,
                Permohonan::STATUS_MENUNGGU_TANDATANGAN,
                Permohonan::STATUS_PASCA_TANDATANGAN,
            ])->count(),
            'pelaksanaan' => (clone $base)->where('status', Permohonan::STATUS_PELAKSANAAN)->count(),
            'dicabut' => (clone $base)->where('status', Permohonan::STATUS_DICABUT)->count(),
            'ditolak' => (clone $base)->where('status', Permohonan::STATUS_DITOLAK)->count(),
        ];

        $bulanLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];
        $statusCountsByMonth = (clone $base)
            ->selectRaw('MONTH(created_at) as bulan, COUNT(*) as total')
            ->groupBy('bulan')
            ->pluck('total', 'bulan')
            ->toArray();
        $chartTrend = [];
        for ($i = 1; $i <= 12; $i++) {
            $chartTrend[] = ['bulan' => $bulanLabels[$i - 1], 'total' => $statusCountsByMonth[$i] ?? 0];
        }

        // Antrean tindakan TKKSD — tertua duluan
        $needsValidasi = Permohonan::with(['kategori', 'operator', 'kota'])
            ->where('status', Permohonan::STATUS_PERMOHONAN)
            ->oldest()
            ->limit(5)
            ->get();

        $needsPembahasan = Permohonan::with(['kategori', 'operator', 'kota'])
            ->where('status', Permohonan::STATUS_PEMBAHASAN)
            ->oldest()
            ->limit(5)
            ->get();

        $needsJadwal = Permohonan::with(['kategori', 'operator', 'kota', 'penjadwalans'])
            ->where('status', Permohonan::STATUS_PENJADWALAN)
            ->oldest()
            ->limit(5)
            ->get();

        return Inertia::render('Backend/DashboardTkksd', [
            'stats' => $stats,
            'chartTrend' => $chartTrend,
            'needsValidasi' => $needsValidasi,
            'needsPembahasan' => $needsPembahasan,
            'needsJadwal' => $needsJadwal,
            'tahun' => (int) $tahun,
        ]);
    }

    /**
     * Dashboard untuk TKKSD Lokus Kerjasama.
     * Hanya tampilkan monev yang menunggu approval dan kerjasama OPD-nya.
     */
    protected function dashboardTkksdLokus(Request $request)
    {
        $user = $request->user();
        $opdId = $user->id_opd;
        $opd = $opdId ? \App\Models\Opd::find($opdId) : null;

        $kerjasamaQuery = Permohonan::query()
            ->whereHas('opds', fn($q) => $q->where('opd.id', $opdId))
            ->whereIn('status', [Permohonan::STATUS_PELAKSANAAN, Permohonan::STATUS_SELESAI]);

        $monevQuery = \App\Models\Monev::query()
            ->whereHas('permohonan.opds', fn($q) => $q->where('opd.id', $opdId));

        $stats = [
            'kerjasama_aktif' => (clone $kerjasamaQuery)->where('status', Permohonan::STATUS_PELAKSANAAN)->count(),
            'kerjasama_selesai' => (clone $kerjasamaQuery)->where('status', Permohonan::STATUS_SELESAI)->count(),
            'monev_menunggu' => (clone $monevQuery)
                ->where('status', \App\Models\Monev::STATUS_SUBMITTED)
                ->whereNull('tkksd_approved_at')
                ->count(),
            'monev_disetujui' => (clone $monevQuery)
                ->whereNotNull('tkksd_approved_at')
                ->count(),
        ];

        // Monev yang menunggu review user ini (TKKSD Lokus)
        $pendingMonev = \App\Models\Monev::with(['permohonan.kategori', 'permohonan.opds', 'pemohon'])
            ->whereHas('permohonan.opds', fn($q) => $q->where('opd.id', $opdId))
            ->where('status', \App\Models\Monev::STATUS_SUBMITTED)
            ->whereNull('tkksd_approved_at')
            ->latest()
            ->limit(10)
            ->get();

        // Kerjasama OPD yang sudah lewat tanggal_berakhir tapi belum dimonev — perlu didorong ke pemohon
        $perluDimonev = Permohonan::with(['kategori', 'operator'])
            ->whereHas('opds', fn($q) => $q->where('opd.id', $opdId))
            ->whereIn('status', [Permohonan::STATUS_PELAKSANAAN, Permohonan::STATUS_SELESAI])
            ->whereNotNull('tanggal_berakhir')
            ->where('tanggal_berakhir', '<=', now())
            ->whereDoesntHave('monev')
            ->latest()
            ->limit(10)
            ->get();

        return Inertia::render('Backend/DashboardTkksdLokus', [
            'stats' => $stats,
            'opd' => $opd,
            'pendingMonev' => $pendingMonev,
            'perluDimonev' => $perluDimonev,
        ]);
    }
}
