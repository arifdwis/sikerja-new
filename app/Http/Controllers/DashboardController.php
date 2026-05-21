<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use App\Models\Kategori;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $tahun = $request->get('tahun', date('Y'));
        $kategoriId = $request->get('kategori_id');
        $bulan = $request->get('bulan');

        $user = $request->user();
        $isPemohon = $user->hasRole('pemohon');

        if ($user->hasRole('tkksd') && !$user->hasRole(['administrator', 'superadmin'])) {
            return redirect()->route('pembahasan.index');
        }

        $query = Permohonan::whereYear('created_at', $tahun);

        if ($kategoriId) {
            $query->where('id_kategori', $kategoriId);
        }

        if ($bulan) {
            $query->whereMonth('created_at', $bulan);
        }
        if ($isPemohon) {
            $query->where('id_pemohon_0', $user->id);
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
            'disetujui' => $statusCounts[Permohonan::STATUS_DISETUJUI] ?? 0,
            'selesai' => $statusCounts[Permohonan::STATUS_SELESAI] ?? 0,
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
        if ($isPemohon) {
            $trendLastYear->where('id_pemohon_0', $user->id);
        }

        // Expiring Agreements Alert (e.g., expiring in 30 days)
        $alerts = [];
        if ($isPemohon) {
            $expiringSoon = Permohonan::where('id_pemohon_0', $user->id)
                ->where('status', Permohonan::STATUS_SELESAI) // Assuming only active ones matter
                ->whereNotNull('tanggal_berakhir')
                ->where('tanggal_berakhir', '>', now())
                ->where('tanggal_berakhir', '<=', now()->addDays(30))
                ->get();

            foreach ($expiringSoon as $item) {
                $daysLeft = now()->diffInDays(\Carbon\Carbon::parse($item->tanggal_berakhir));
                $alerts[] = [
                    'type' => 'warning',
                    'message' => "Kerjasama '{$item->label}' akan berakhir dalam {$daysLeft} hari lagi ({$item->tanggal_berakhir}).",
                    'link' => route('permohonan.show', $item->uuid)
                ];
            }

            // Check for Expired but not renewed?
            $expired = Permohonan::where('id_pemohon_0', $user->id)
                ->where('status', Permohonan::STATUS_SELESAI)
                ->whereNotNull('tanggal_berakhir')
                ->where('tanggal_berakhir', '<', now())
                ->limit(3)
                ->get();

            foreach ($expired as $item) {
                $alerts[] = [
                    'type' => 'error',
                    'message' => "Kerjasama '{$item->label}' telah berakhir pada {$item->tanggal_berakhir}.",
                    'link' => route('permohonan.show', $item->uuid)
                ];
            }
        }

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
        if ($isPemohon) {
            $baseCatQuery->where('id_pemohon_0', $user->id);
        }

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

        if ($isPemohon) {
            $kategoris = Kategori::all();
            $provinsis = \App\Models\Provinsi::all();
            $pemohon = $user->pemohon;
            $corporate = $user->corporate;
            $pemohonanList = Permohonan::where('id_pemohon_0', $user->id) // Or logic to get available signers
                ->orWhere('id_pemohon_1', $user->id)
                ->get(); // Simplified for now, refine as needed for PPKSD-2

            return Inertia::render('Backend/DashboardPemohon', [
                'stats' => $stats,
                'chartTrend' => $chartTrend,
                'chartStatus' => $chartStatus,
                'permohonanTerbaru' => $permohonanTerbaru,
                'kategoris' => $kategoris,
                'provinsis' => $provinsis,
                'pemohon' => $pemohon,
                'corporate' => $corporate,
                'pemohonanList' => $pemohonanList,
                'alerts' => $alerts,
            ]);
        }

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
}
