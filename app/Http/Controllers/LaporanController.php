<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

use App\Models\Kategori;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Filter Data Pre-fetch
        $availableYears = Permohonan::selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->toArray();

        // Ensure current year is in list if empty
        if (empty($availableYears)) {
            $availableYears = [date('Y')];
        }

        $kategoris = Kategori::select('id', 'label')->orderBy('label')->get();

        $query = Permohonan::with(['kategori', 'pemohon', 'kota'])
            ->whereNotNull('tanggal_mulai')
            ->whereNotNull('tanggal_berakhir')
            ->where('status', Permohonan::STATUS_SELESAI); // Only monitor finished agreements? Or all active? Usually 'Selesai' means Signed/Active.

        // If pemohon, strict ownership
        if ($user->hasRole('pemohon')) {
            $query->where('id_pemohon_0', $user->id);
        }

        // Apply filters
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('label', 'like', "%{$search}%")
                    ->orWhere('nama_instansi', 'like', "%{$search}%");
            });
        }

        if ($request->has('tahun') && $request->tahun) {
            // Filter based on active period during that year? Or just created?
            // Usually Laporan monitors active agreements. 
            // Logic: Agreement is active IF (start <= Year-12-31 AND end >= Year-01-01)
            // But simpler for now: created_at year if user wants simple filter, OR active during that year.
            // Let's go with 'Active in Year' logic if possible, OR just created_at.
            // Given the context of "Monitoring", likely strictly active.
            // But usually dashboards filter by "Year" implies "Data for that year".
            // Let's stick to created_at or start_date year for consistency with dashboard.
            $query->whereYear('tanggal_mulai', $request->tahun);
        }

        if ($request->has('kategori_id') && $request->kategori_id) {
            $query->where('id_kategori', $request->kategori_id);
        }

        if ($request->has('bulan') && $request->bulan) {
            $query->whereMonth('tanggal_mulai', $request->bulan);
        }

        $data = $query->get()->map(function ($item) {
            $start = Carbon::parse($item->tanggal_mulai);
            $end = Carbon::parse($item->tanggal_berakhir);
            $now = Carbon::now();

            $daysRemaining = $now->diffInDays($end, false);
            $totalDuration = $start->diffInDays($end);

            // Determine status based on time
            $statusMonev = 'Berlangsung';
            $alertLevel = 'success'; // Green

            if ($daysRemaining < 0) {
                $statusMonev = 'Berakhir';
                $alertLevel = 'danger'; // Red
            } elseif ($daysRemaining <= 90) {
                $statusMonev = 'Segera Berakhir';
                $alertLevel = 'warning'; // Yellow/Orange
            }

            return [
                'id' => $item->id,
                'uuid' => $item->uuid,
                'judul' => $item->label,
                'instansi' => $item->nama_instansi,
                'kategori' => $item->kategori->label ?? '-',
                'mulai' => $start->format('d M Y'),
                'berakhir' => $end->format('d M Y'),
                'sisa_hari' => (int) $daysRemaining,
                'total_durasi' => $totalDuration,
                'status_monev' => $statusMonev,
                'alert_level' => $alertLevel,
                'progress' => $totalDuration > 0 ? min(100, max(0, ($start->diffInDays($now) / $totalDuration) * 100)) : 0
            ];
        });

        // Group by Status for Tabs
        $grouped = [
            'berlangsung' => $data->where('status_monev', 'Berlangsung')->values(),
            'segera_berakhir' => $data->where('status_monev', 'Segera Berakhir')->values(),
            'berakhir' => $data->where('status_monev', 'Berakhir')->values(),
        ];

        return Inertia::render('Backend/Laporan/Index', [
            'laporan' => $grouped,
            'summary' => [
                'total' => $data->count(),
                'berlangsung' => $grouped['berlangsung']->count(),
                'segera_berakhir' => $grouped['segera_berakhir']->count(),
                'berakhir' => $grouped['berakhir']->count(),
            ],
            'filters' => $request->only(['search', 'tahun', 'bulan', 'kategori_id']),
            'availableYears' => $availableYears,
            'filterCategories' => $kategoris,
        ]);
    }

    public function akumulatif(Request $request)
    {
        $query = Permohonan::query()
            ->where('status', Permohonan::STATUS_SELESAI) // Only count valid/signed agreements
            ->selectRaw('YEAR(tanggal_mulai) as tahun')
            ->selectRaw('COUNT(*) as total_kerjasama')
            ->selectRaw('SUM(CASE WHEN tanggal_berakhir >= CURDATE() THEN 1 ELSE 0 END) as aktif')
            ->selectRaw('SUM(CASE WHEN tanggal_berakhir < CURDATE() THEN 1 ELSE 0 END) as selesai')
            ->groupBy('tahun')
            ->orderByDesc('tahun');

        if ($request->has('tahun') && $request->tahun) {
            $query->whereYear('tanggal_mulai', $request->tahun);
        }

        return Inertia::render('Backend/Laporan/Akumulatif', [
            'data' => $query->get(),
            'filters' => $request->all(),
        ]);
    }

    public function rekapMitra(Request $request)
    {
        // Top 20 Mitra by count
        $query = Permohonan::query()
            ->where('status', Permohonan::STATUS_SELESAI)
            ->select('nama_instansi as mitra')
            ->selectRaw('COUNT(*) as total_kerjasama')
            ->groupBy('nama_instansi')
            ->orderByDesc('total_kerjasama')
            ->limit(20);

        return Inertia::render('Backend/Laporan/RekapMitra', [
            'data' => $query->get()->map(function ($item, $index) {
                $item->id = $index + 1;
                $item->jenis = '-'; // Placeholder as data not explicitly available
                return $item;
            }),
            'filters' => $request->all(),
        ]);
    }

    public function persentaseOpd(Request $request)
    {
        // Aggregate by Pemohon (OPD)
        // Need to join with Pemohon/User table to get name
        // Assuming Pemohon model has 'name' or User has 'name'

        $total = Permohonan::where('status', Permohonan::STATUS_SELESAI)->count();
        if ($total == 0)
            $total = 1;

        $data = Permohonan::with(['pemohon', 'operator']) // Load pemohon to get unit_kerja
            ->where('status', Permohonan::STATUS_SELESAI)
            ->get()
            ->groupBy('id_pemohon_0')
            ->map(function ($group) use ($total) {
                $first = $group->first();

                // Prioritize Pemohon's Unit Kerja, then Nama Instansi, then Operator Name
                $name = 'N/A';
                if ($first->pemohon) {
                    $name = $first->pemohon->unit_kerja ?? $first->pemohon->nama_instansi;
                }

                if (!$name && $first->operator) {
                    $name = $first->operator->name;
                }

                $count = $group->count();

                return [
                    'opd' => $name ?? 'Unknown',
                    'realisasi' => $count,
                    'persentase' => round(($count / $total) * 100, 2)
                ];
            })->values()->sortByDesc('realisasi');

        return Inertia::render('Backend/Laporan/PersentaseOpd', [
            'data' => $data->values(),
            'filters' => $request->all(),
        ]);
    }

    public function persentaseBidang(Request $request)
    {
        $total = Permohonan::where('status', Permohonan::STATUS_SELESAI)->count();
        if ($total == 0)
            $total = 1;

        $data = Kategori::withCount([
            'permohonans' => function ($q) {
                $q->where('status', Permohonan::STATUS_SELESAI);
            }
        ])
            ->get()
            ->map(function ($file) use ($total) {
                return [
                    'bidang' => $file->label,
                    'jumlah' => $file->permohonans_count,
                    'persentase' => round(($file->permohonans_count / $total) * 100, 2)
                ];
            })
            ->sortByDesc('jumlah')
            ->values();

        return Inertia::render('Backend/Laporan/PersentaseBidang', [
            'data' => $data,
            'filters' => $request->all(),
        ]);
    }

    public function cetakDetail($uuid)
    {
        $item = Permohonan::with(['kategori', 'pemohon', 'operator', 'kota', 'provinsi'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        return Inertia::render('Backend/Laporan/CetakDetail', [
            'data' => $item
        ]);
    }

    public function cetakSemua()
    {
        $items = Permohonan::with(['kategori', 'pemohon', 'operator', 'kota', 'provinsi'])
            ->where('status', Permohonan::STATUS_SELESAI)
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('Backend/Laporan/CetakSemua', [
            'data' => $items
        ]);
    }
}
