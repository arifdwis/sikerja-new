<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\Kategori;
use App\Models\Pemohon;
use App\Models\User;
use App\Models\KerjasamaManual;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Filter Data Pre-fetch: Gabungkan tahun dari Permohonan dan KerjasamaManual
        $tahunListSistem = Permohonan::whereNotNull('tanggal_mulai')
            ->selectRaw('DISTINCT YEAR(tanggal_mulai) as year')
            ->pluck('year');
        $tahunListManual = KerjasamaManual::whereNotNull('tanggal_mulai')
            ->selectRaw('DISTINCT YEAR(tanggal_mulai) as year')
            ->pluck('year');

        $availableYears = $tahunListSistem->concat($tahunListManual)
            ->filter()
            ->unique()
            ->sortDesc()
            ->values()
            ->toArray();

        // Ensure current year is in list if empty
        if (empty($availableYears)) {
            $availableYears = [(int) date('Y')];
        }

        $kategoris = Kategori::select('id', 'label')->orderBy('label')->get();

        // Query Permohonan Selesai
        $queryPermohonan = Permohonan::select([
            'id',
            'uuid',
            'label',
            'nama_instansi',
            'id_kategori',
            'tanggal_mulai',
            'tanggal_berakhir',
        ])
            ->with(['kategori:id,label'])
            ->whereNotNull('tanggal_mulai')
            ->whereNotNull('tanggal_berakhir')
            ->where('status', Permohonan::STATUS_SELESAI);

        // Query Kerjasama Manual
        $queryManual = KerjasamaManual::select([
            'id',
            'uuid',
            'nomor_pks',
            'label',
            'nama_instansi',
            'id_kategori',
            'tanggal_mulai',
            'tanggal_berakhir',
        ])
            ->with(['kategori:id,label', 'opds:id,nama,singkatan'])
            ->whereNotNull('tanggal_mulai')
            ->whereNotNull('tanggal_berakhir');

        // If pemohon, strict ownership
        if ($user->hasRole('pemohon')) {
            $queryPermohonan->where('id_pemohon_0', $user->id);

            if ($user->id_opd) {
                $queryManual->where(function ($q) use ($user) {
                    $q->whereHas('opds', fn($opdQuery) => $opdQuery->where('opd.id', $user->id_opd))
                      ->orWhere('created_by', $user->id);
                });
            } else {
                $queryManual->where('created_by', $user->id);
            }
        }

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $queryPermohonan->where(function ($q) use ($search) {
                $q->where('label', 'like', "%{$search}%")
                    ->orWhere('nama_instansi', 'like', "%{$search}%");
            });

            $queryManual->where(function ($q) use ($search) {
                $q->where('label', 'like', "%{$search}%")
                    ->orWhere('nama_instansi', 'like', "%{$search}%")
                    ->orWhere('nomor_pks', 'like', "%{$search}%");
            });
        }

        if ($request->filled('tahun')) {
            $queryPermohonan->whereYear('tanggal_mulai', $request->tahun);
            $queryManual->whereYear('tanggal_mulai', $request->tahun);
        }

        if ($request->filled('kategori_id')) {
            $queryPermohonan->where('id_kategori', $request->kategori_id);
            $queryManual->where('id_kategori', $request->kategori_id);
        }

        if ($request->filled('bulan')) {
            $queryPermohonan->whereMonth('tanggal_mulai', $request->bulan);
            $queryManual->whereMonth('tanggal_mulai', $request->bulan);
        }

        $now = Carbon::now()->startOfDay();

        $mapRow = function ($item, string $source) use ($now) {
            $start = Carbon::parse($item->tanggal_mulai)->startOfDay();
            $end = Carbon::parse($item->tanggal_berakhir)->startOfDay();

            $daysRemaining = (int) $now->diffInDays($end, false);
            $totalDuration = (int) $start->diffInDays($end);

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

            $elapsedDays = (int) $start->diffInDays($now, false);
            $progress = 0;
            if ($totalDuration > 0) {
                $progress = min(100, max(0, ($elapsedDays / $totalDuration) * 100));
            }

            return [
                'id' => $item->id,
                'uuid' => $item->uuid,
                'source' => $source,
                'nomor_pks' => $item->nomor_pks ?? null,
                'judul' => $item->label,
                'instansi' => $item->nama_instansi,
                'kategori' => $item->kategori->label ?? '-',
                'mulai' => $start->format('d M Y'),
                'berakhir' => $end->format('d M Y'),
                'sisa_hari' => $daysRemaining,
                'total_durasi' => $totalDuration,
                'status_monev' => $statusMonev,
                'alert_level' => $alertLevel,
                'progress' => $progress,
            ];
        };

        $dataSistem = $queryPermohonan->get()->map(fn($item) => $mapRow($item, 'sistem'));
        $dataManual = $queryManual->get()->map(fn($item) => $mapRow($item, 'manual'));

        $data = $dataSistem->concat($dataManual);

        // Group by Status for Tabs
        $grouped = [
            'berlangsung' => $data->where('status_monev', 'Berlangsung')->sortBy('sisa_hari')->values(),
            'segera_berakhir' => $data->where('status_monev', 'Segera Berakhir')->sortBy('sisa_hari')->values(),
            'berakhir' => $data->where('status_monev', 'Berakhir')->sortByDesc('sisa_hari')->values(),
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

    public function statistik(Request $request)
    {
        // Build a unified collection: Permohonan (semua yang punya periode kerjasama) + KerjasamaManual.
        // Logika "kerjasama valid": punya tanggal_mulai DAN tanggal_berakhir (artinya sudah disepakati & ditandatangani),
        // tidak peduli stuck di status alur sistem berapa pun. Plus tabel kerjasama_manual untuk data sistem lama.
        // Tetap include yang tanggalnya belum ada juga, supaya pengguna lihat permohonan migrasi sistem lama.
        $tahunFilter = $request->input('tahun');

        $permohonanRows = Permohonan::query()
            ->whereNotIn('status', [Permohonan::STATUS_DITOLAK, Permohonan::STATUS_DICABUT])
            ->select([
                'id', 'uuid', 'label', 'nama_instansi', 'id_kategori',
                'id_pemohon_0', 'tanggal_mulai', 'tanggal_berakhir', 'created_at', 'status',
            ])
            ->with(['kategori:id,label', 'opds:id,nama,singkatan'])
            ->get()
            ->map(fn($p) => [
                'source'         => 'sistem',
                'id'             => $p->id,
                'label'          => $p->label,
                'mitra'          => $p->nama_instansi,
                'id_kategori'    => $p->id_kategori,
                'kategori_label' => $p->kategori?->label,
                'tanggal_mulai'  => optional($p->tanggal_mulai)->toDateString(),
                'tanggal_berakhir' => optional($p->tanggal_berakhir)->toDateString(),
                'created_at'     => optional($p->created_at)->toDateString(),
                'opds'           => $p->opds->map(fn($o) => $o->singkatan ?: $o->nama)->all(),
                'id_pemohon_0'   => $p->id_pemohon_0,
            ]);

        $manualRows = \App\Models\KerjasamaManual::query()
            ->select([
                'id', 'uuid', 'label', 'nama_instansi', 'id_kategori',
                'tanggal_mulai', 'tanggal_berakhir', 'created_at',
            ])
            ->with(['kategori:id,label', 'opds:id,nama,singkatan'])
            ->get()
            ->map(fn($m) => [
                'source'         => 'manual',
                'id'             => $m->id,
                'label'          => $m->label,
                'mitra'          => $m->nama_instansi,
                'id_kategori'    => $m->id_kategori,
                'kategori_label' => $m->kategori?->label,
                'tanggal_mulai'  => optional($m->tanggal_mulai)->toDateString(),
                'tanggal_berakhir' => optional($m->tanggal_berakhir)->toDateString(),
                'created_at'     => optional($m->created_at)->toDateString(),
                'opds'           => $m->opds->map(fn($o) => $o->singkatan ?: $o->nama)->all(),
                'id_pemohon_0'   => null,
            ]);

        $all = $permohonanRows->concat($manualRows);

        // Helper: tahun acuan = tanggal_mulai bila ada, fallback ke created_at
        $resolveYear = function ($row) {
            $date = $row['tanggal_mulai'] ?: $row['created_at'];
            return $date ? (int) substr($date, 0, 4) : null;
        };

        if ($tahunFilter) {
            $all = $all->filter(fn($r) => $resolveYear($r) === (int) $tahunFilter)->values();
        }

        $now = Carbon::now()->startOfDay();

        // 1) Akumulatif per tahun (aktif vs selesai vs tanpa-jangka-waktu)
        $akumulatifData = $all
            ->groupBy(fn($r) => $resolveYear($r) ?? 'Tanpa Tahun')
            ->map(function ($rows, $tahun) use ($now) {
                $total = $rows->count();
                $aktif = $rows->filter(fn($r) => $r['tanggal_berakhir'] && Carbon::parse($r['tanggal_berakhir']) >= $now)->count();
                $selesai = $rows->filter(fn($r) => $r['tanggal_berakhir'] && Carbon::parse($r['tanggal_berakhir']) < $now)->count();
                $tanpaJangka = $rows->filter(fn($r) => !$r['tanggal_berakhir'])->count();
                return [
                    'tahun'           => $tahun,
                    'total_kerjasama' => $total,
                    'aktif'           => $aktif,
                    'selesai'         => $selesai,
                    'tanpa_jangka'    => $tanpaJangka,
                ];
            })
            ->sortKeysDesc()
            ->values();

        // 2) Rekap mitra (Top 20) — hitung dari kedua sumber
        $rekapMitraData = $all
            ->groupBy(fn($r) => $r['mitra'] ?: 'Tidak Diketahui')
            ->map(fn($rows, $mitra) => [
                'mitra'           => $mitra,
                'total_kerjasama' => $rows->count(),
                'sistem'          => $rows->where('source', 'sistem')->count(),
                'manual'          => $rows->where('source', 'manual')->count(),
            ])
            ->sortByDesc('total_kerjasama')
            ->take(20)
            ->values()
            ->map(function ($item, $i) {
                $item['id'] = $i + 1;
                return $item;
            });

        // 3) Persentase OPD — gunakan opds (multi) yang sudah di-flatten
        $totalAll = $all->count();
        $opdCounter = [];
        foreach ($all as $row) {
            if (empty($row['opds'])) {
                $opdCounter['(Tanpa OPD)'] = ($opdCounter['(Tanpa OPD)'] ?? 0) + 1;
                continue;
            }
            foreach ($row['opds'] as $opd) {
                $opdCounter[$opd] = ($opdCounter[$opd] ?? 0) + 1;
            }
        }
        $persentaseOpdData = collect($opdCounter)
            ->map(fn($count, $opd) => [
                'opd'        => $opd,
                'realisasi'  => $count,
                'persentase' => $totalAll > 0 ? round(($count / $totalAll) * 100, 2) : 0,
            ])
            ->sortByDesc('realisasi')
            ->values();

        // 4) Persentase Bidang — group berdasarkan kategori_label
        $persentaseBidangData = $all
            ->groupBy(fn($r) => $r['kategori_label'] ?: 'Tanpa Kategori')
            ->map(fn($rows, $kategori) => [
                'bidang'     => $kategori,
                'jumlah'     => $rows->count(),
                'sistem'     => $rows->where('source', 'sistem')->count(),
                'manual'     => $rows->where('source', 'manual')->count(),
                'persentase' => $totalAll > 0 ? round(($rows->count() / $totalAll) * 100, 2) : 0,
            ])
            ->sortByDesc('jumlah')
            ->values();

        // 5) Ringkasan global (untuk KPI cards di atas tabs)
        $summary = [
            'total'        => $totalAll,
            'sistem'       => $all->where('source', 'sistem')->count(),
            'manual'       => $all->where('source', 'manual')->count(),
            'aktif'        => $all->filter(fn($r) => $r['tanggal_berakhir'] && Carbon::parse($r['tanggal_berakhir']) >= $now)->count(),
            'selesai'      => $all->filter(fn($r) => $r['tanggal_berakhir'] && Carbon::parse($r['tanggal_berakhir']) < $now)->count(),
            'tanpa_jangka' => $all->filter(fn($r) => !$r['tanggal_berakhir'])->count(),
            'mitra_unik'   => $all->pluck('mitra')->filter()->unique()->count(),
        ];

        // Daftar tahun yang tersedia (gabungan)
        $tahunListSistem = Permohonan::whereNotIn('status', [Permohonan::STATUS_DITOLAK, Permohonan::STATUS_DICABUT])
            ->selectRaw('DISTINCT YEAR(COALESCE(tanggal_mulai, created_at)) as y')
            ->pluck('y');
        $tahunListManual = \App\Models\KerjasamaManual::selectRaw('DISTINCT YEAR(COALESCE(tanggal_mulai, created_at)) as y')->pluck('y');
        $availableYears = $tahunListSistem->concat($tahunListManual)->filter()->unique()->sortDesc()->values();

        return Inertia::render('Backend/Laporan/Statistik', [
            'summary'              => $summary,
            'akumulatifData'       => $akumulatifData,
            'rekapMitraData'       => $rekapMitraData,
            'persentaseOpdData'    => $persentaseOpdData,
            'persentaseBidangData' => $persentaseBidangData,
            'availableYears'       => $availableYears,
            'filters'              => $request->only(['tahun']),
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

        $opdCounts = Permohonan::where('status', Permohonan::STATUS_SELESAI)
            ->select('id_pemohon_0', DB::raw('count(*) as total'))
            ->groupBy('id_pemohon_0')
            ->get();

        $opdIds = $opdCounts->pluck('id_pemohon_0')->filter()->values();
        $pemohonByOperator = Pemohon::whereIn('id_operator', $opdIds)->get()->keyBy('id_operator');
        $userById = User::whereIn('id', $opdIds)->get()->keyBy('id');

        $data = $opdCounts
            ->map(function ($row) use ($pemohonByOperator, $userById, $total) {
                $pemohon = $pemohonByOperator->get($row->id_pemohon_0);
                $user = $userById->get($row->id_pemohon_0);

                $name = 'N/A';
                if ($pemohon) {
                    $name = $pemohon->unit_kerja ?? $pemohon->nama_instansi;
                }

                if (!$name && $user) {
                    $name = $user->name;
                }

                $count = (int) $row->total;

                return [
                    'opd' => $name ?? 'Unknown',
                    'realisasi' => $count,
                    'persentase' => round(($count / $total) * 100, 2)
                ];
            })
            ->values()
            ->sortByDesc('realisasi');

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
            ->first();

        if (!$item) {
            $km = KerjasamaManual::with(['kategori', 'opds', 'creator'])
                ->where('uuid', $uuid)
                ->firstOrFail();

            $opdNames = $km->opds->map(fn($o) => $o->singkatan ?: $o->nama)->implode(', ');
            $item = (object) [
                'id' => $km->id,
                'uuid' => $km->uuid,
                'nomor_permohonan' => $km->nomor_pks ?: 'PKS-MANUAL',
                'label' => $km->label,
                'nama_instansi' => $km->nama_instansi,
                'alamat' => '-',
                'latar_belakang' => '-',
                'maksud_tujuan' => '-',
                'ruang_lingkup' => $km->ruang_lingkup ?: '-',
                'manfaat' => '-',
                'jangka_waktu' => $km->jangka_waktu ?: '-',
                'tanggal_mulai' => $km->tanggal_mulai,
                'tanggal_berakhir' => $km->tanggal_berakhir,
                'created_at' => $km->created_at,
                'status_label' => (object) [
                    'label' => 'Selesai (PKS Manual)'
                ],
                'kategori' => $km->kategori,
                'operator' => (object) [
                    'name' => $opdNames ?: ($km->creator?->name ?? 'Pemerintah Kota Samarinda')
                ],
                'pemohon' => (object) [
                    'name' => $km->nama_instansi
                ],
            ];
        }

        return Inertia::render('Backend/Laporan/CetakDetail', [
            'data' => $item
        ]);
    }

    public function cetakSemua()
    {
        $itemsPermohonan = Permohonan::with(['kategori', 'pemohon', 'operator', 'kota', 'provinsi'])
            ->where('status', Permohonan::STATUS_SELESAI)
            ->whereNotNull('tanggal_mulai')
            ->whereNotNull('tanggal_berakhir')
            ->get()
            ->map(function ($p) {
                return (object) [
                    'id' => $p->id,
                    'uuid' => $p->uuid,
                    'label' => $p->label,
                    'kategori' => $p->kategori,
                    'pemohon' => $p->pemohon,
                    'nama_instansi' => $p->nama_instansi,
                    'alamat' => $p->alamat,
                    'tanggal_mulai' => $p->tanggal_mulai,
                    'tanggal_berakhir' => $p->tanggal_berakhir,
                    'created_at' => $p->created_at,
                    'source' => 'sistem',
                ];
            });

        $itemsManual = KerjasamaManual::with(['kategori', 'opds', 'creator'])
            ->whereNotNull('tanggal_mulai')
            ->whereNotNull('tanggal_berakhir')
            ->get()
            ->map(function ($km) {
                $opdNames = $km->opds->map(fn($o) => $o->singkatan ?: $o->nama)->implode(', ');
                return (object) [
                    'id' => 'm_' . $km->id,
                    'uuid' => $km->uuid,
                    'label' => $km->label . ($km->nomor_pks ? ' (' . $km->nomor_pks . ')' : ''),
                    'kategori' => $km->kategori,
                    'pemohon' => (object) [
                        'name' => $opdNames ?: ($km->creator?->name ?? 'Kerjasama Manual')
                    ],
                    'nama_instansi' => $km->nama_instansi,
                    'alamat' => $km->ruang_lingkup ? 'Ruang lingkup: ' . \Illuminate\Support\Str::limit($km->ruang_lingkup, 100) : '-',
                    'tanggal_mulai' => $km->tanggal_mulai,
                    'tanggal_berakhir' => $km->tanggal_berakhir,
                    'created_at' => $km->created_at,
                    'source' => 'manual',
                ];
            });

        $items = $itemsPermohonan->concat($itemsManual)
            ->sortByDesc('created_at')
            ->values();

        return Inertia::render('Backend/Laporan/CetakSemua', [
            'data' => $items
        ]);
    }
}
