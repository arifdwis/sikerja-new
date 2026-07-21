<?php

namespace App\Http\Controllers;

use App\Models\Monev;
use App\Models\MonevAdminDetail;
use App\Models\MonevPemohonDetail;
use App\Models\MonevTkksdDetail;
use App\Models\Notifikasi;
use App\Models\Permohonan;
use App\Models\Pemohon;
use App\Services\WhatsappService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class MonevController extends Controller implements HasMiddleware
{
    protected $title;
    protected $prefix;
    protected $view;
    protected $share;

    public function __construct()
    {
        $this->title = 'Monitoring & Evaluasi';
        $this->view = 'Backend/Monev';
        $this->prefix = 'monev';

        $this->share = [
            'title' => $this->title,
            'view' => $this->view,
            'prefix' => $this->prefix
        ];
    }

    public static function middleware(): array
    {
        return [
            new Middleware('can:monev.menu', only: ['index']),
            new Middleware('can:monev.create', only: ['create', 'store']),
            new Middleware('can:monev.view', only: ['show']),
            new Middleware('can:monev.review', only: ['review', 'notifyPemohon']),
        ];
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user->can('monev.menu.admin');
        $isTkksdLokus = $user->hasRole('tkksd_lokus') && !$user->hasRole(['administrator', 'superadmin']);

        if ($isAdmin) {
            // Admin: 1 baris per kerjasama (dedup), eager-load semua monev (pemohon + tkksd_lokus + admin)
            $permQuery = Permohonan::with([
                    'kategori',
                    'pemohon',
                    'opds',
                    'monevs.submitter',
                    'monevs.pemohon',
                    'monevs.reviewer',
                    'monevs.adminDetail',
                    'monevs.pemohonDetail',
                    'monevs.tkksdDetail',
                ])
                ->whereHas('monevs')
                ->orderByDesc('created_at');

            if ($request->has('search')) {
                $search = $request->search;
                $permQuery->where(function ($q) use ($search) {
                    $q->where('label', 'like', "%{$search}%")
                        ->orWhere('nama_instansi', 'like', "%{$search}%")
                        ->orWhereHas('monevs', fn($q2) => $q2->where('kode_monev', 'like', "%{$search}%"));
                });
            }

            $paginated = $permQuery->paginate(10)->withQueryString();

            // Bentuk row data: 1 row = 1 kerjasama dengan koleksi monev[]
            $datas = $paginated->through(function (Permohonan $p) {
                $monevs = $p->monevs->sortBy('created_at')->values();
                $rolesPresent = $monevs->pluck('submitter_role')->filter()->unique()->values();
                $latest = $monevs->sortByDesc('created_at')->first();

                return [
                    // Identitas (untuk kolom Kerjasama)
                    'id' => $p->id,
                    'uuid' => $p->uuid,
                    'permohonan' => [
                        'id' => $p->id,
                        'uuid' => $p->uuid,
                        'label' => $p->label,
                        'nama_instansi' => $p->nama_instansi,
                        'kategori' => $p->kategori,
                        'opds' => $p->opds->map(fn($o) => [
                            'id' => $o->id,
                            'nama' => $o->nama,
                            'singkatan' => $o->singkatan,
                        ])->values(),
                    ],
                    // Monev list lengkap untuk modal Detail
                    'monevs' => $monevs->map(fn($m) => [
                        'id' => $m->id,
                        'uuid' => $m->uuid,
                        'kode_monev' => $m->kode_monev,
                        'submitter_role' => $m->submitter_role,
                        'submitter_name' => $m->submitter?->name ?? '-',
                        'tanggal_evaluasi' => $m->tanggal_evaluasi,
                        'rekomendasi_lanjutan' => $m->rekomendasi_lanjutan,
                        'rating' => $m->rating,
                        'kesesuaian_tujuan' => $m->kesesuaian_tujuan,
                        'ketepatan_waktu' => $m->ketepatan_waktu,
                        'kontribusi_mitra' => $m->kontribusi_mitra,
                        'tingkat_koordinasi' => $m->tingkat_koordinasi,
                        'capaian_indikator' => $m->capaian_indikator,
                        'dampak_pelaksanaan' => $m->dampak_pelaksanaan,
                        'inovasi_manfaat' => $m->inovasi_manfaat,
                        'kelengkapan_dokumen' => $m->kelengkapan_dokumen,
                        'pelaporan_berkala' => $m->pelaporan_berkala,
                        'kendala_administrasi' => $m->kendala_administrasi,
                        'relevansi_kebutuhan' => $m->relevansi_kebutuhan,
                        'saran_rekomendasi' => $m->saran_rekomendasi,
                        'file_bukti' => $m->file_bukti,
                        // Field monev khusus pemohon
                        'pmh_realisasi_kegiatan' => $m->pmh_realisasi_kegiatan,
                        'pmh_kesesuaian_output' => $m->pmh_kesesuaian_output,
                        'pmh_pemanfaatan_hasil' => $m->pmh_pemanfaatan_hasil,
                        'pmh_kendala_lapangan' => $m->pmh_kendala_lapangan,
                        'pmh_keberlanjutan' => $m->pmh_keberlanjutan,
                        'pmh_file_laporan' => $m->pmh_file_laporan,
                        // Field monev khusus TKKSD Lokus
                        'tkl_kepatuhan_pks' => $m->tkl_kepatuhan_pks,
                        'tkl_koordinasi_mitra' => $m->tkl_koordinasi_mitra,
                        'tkl_kesesuaian_anggaran' => $m->tkl_kesesuaian_anggaran,
                        'tkl_temuan_pengawasan' => $m->tkl_temuan_pengawasan,
                        'tkl_rekomendasi_lokus' => $m->tkl_rekomendasi_lokus,
                        'tkl_catatan' => $m->tkl_catatan,
                    ])->values(),
                    // Ringkasan agregat untuk kolom-kolom listing
                    'monev_count' => $monevs->count(),
                    'submitter_roles' => $rolesPresent,
                    'latest_tanggal_evaluasi' => $latest?->tanggal_evaluasi?->toDateString(),
                    'latest_rekomendasi' => $latest?->rekomendasi_lanjutan ?: ($latest?->pmh_keberlanjutan ?: $latest?->tkl_rekomendasi_lokus),
                    'kode_monev' => $monevs->first()?->kode_monev, // representatif untuk pencarian
                ];
            });

            $pendingPermohonans = Permohonan::with(['kategori', 'operator'])
                ->where(function ($q) {
                    $q->where('status', Permohonan::STATUS_SELESAI)
                        ->orWhere(function ($q2) {
                            $q2->where('status', Permohonan::STATUS_PELAKSANAAN)
                                ->whereNotNull('tanggal_berakhir')
                                ->where('tanggal_berakhir', '<=', now());
                        });
                })
                ->whereDoesntHave('monevs', fn($q) => $q->where('submitter_user_id', $user->id))
                ->latest()
                ->get();

            return Inertia::render("$this->view/Index", [
                'datas' => $datas,
                'pendingPermohonans' => $pendingPermohonans,
                'share' => $this->share,
                'filters' => $request->only(['search', 'status', 'detail']),
                'isAdmin' => true,
                'isTkksdLokus' => false,
                'canCreateMonev' => true,
                'adminGrouped' => true,
            ]);
        }

        // Non-admin: tampilan klasik 1 baris per Monev (milik user ini)
        $query = Monev::with([
                'permohonan.kategori', 'permohonan.pemohon', 'permohonan.opds',
                'pemohon', 'reviewer', 'submitter',
                'adminDetail', 'pemohonDetail', 'tkksdDetail',
            ])
            ->latest();

        if ($isTkksdLokus) {
            $query->where('submitter_user_id', $user->id);
        } else {
            // Pemohon: hanya monev miliknya sendiri.
            // Back-compat data lama: submitter_user_id bisa null, maka fallback pakai id_pemohon.
            $pemohonId = Pemohon::where('id_operator', $user->id)->value('id');

            $query->where(function ($q) use ($user, $pemohonId) {
                $q->where('submitter_user_id', $user->id);

                if ($pemohonId) {
                    $q->orWhere(function ($q2) use ($pemohonId) {
                        $q2->whereNull('submitter_user_id')
                            ->where('id_pemohon', $pemohonId);
                    });
                }
            });
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode_monev', 'like', "%{$search}%")
                    ->orWhereHas('permohonan', function ($q2) use ($search) {
                        $q2->where('label', 'like', "%{$search}%")
                            ->orWhere('nama_instansi', 'like', "%{$search}%");
                    });
            });
        }

        $datas = $query->paginate(10)->withQueryString();

        $pendingPermohonans = collect();
        $canCreateMonev = $user->can('monev.create');

        $basePending = Permohonan::with(['kategori', 'operator'])
            ->where(function ($q) {
                $q->where('status', Permohonan::STATUS_SELESAI)
                    ->orWhere(function ($q2) {
                        $q2->where('status', Permohonan::STATUS_PELAKSANAAN)
                            ->whereNotNull('tanggal_berakhir')
                            ->where('tanggal_berakhir', '<=', now());
                    });
            });

        if ($isTkksdLokus) {
            $opdId = $user->id_opd;
            if ($opdId) {
                $pendingPermohonans = (clone $basePending)
                    ->whereHas('opds', fn($q) => $q->where('opd.id', $opdId))
                    ->whereDoesntHave('monevs', fn($q) => $q->where('submitter_user_id', $user->id))
                    ->latest()
                    ->get();
            }
        } elseif ($canCreateMonev) {
            $pendingPermohonans = (clone $basePending)
                ->where('id_pemohon_0', $user->id)
                ->whereDoesntHave('monevs', fn($q) => $q->where('submitter_user_id', $user->id))
                ->latest()
                ->get();
        }

        return Inertia::render("$this->view/Index", [
            'datas' => $datas,
            'pendingPermohonans' => $pendingPermohonans,
            'share' => $this->share,
            'filters' => $request->only(['search', 'status', 'detail']),
            'isAdmin' => false,
            'isTkksdLokus' => $isTkksdLokus,
            'canCreateMonev' => $canCreateMonev,
            'adminGrouped' => false,
        ]);
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        $pemohon = Pemohon::where('id_operator', $user->id)->first();
        $isAdmin = $user->can('monev.menu.admin');
        $isTkksdLokus = $user->hasRole('tkksd_lokus') && !$isAdmin;

        // Daftar permohonan yang user ini boleh monev — beda per role.
        $base = Permohonan::where(function ($q) {
                $q->where('status', Permohonan::STATUS_SELESAI)
                    ->orWhere(function ($q2) {
                        $q2->where('status', Permohonan::STATUS_PELAKSANAAN)
                            ->whereNotNull('tanggal_berakhir')
                            ->where('tanggal_berakhir', '<=', now());
                    });
            })
            ->whereDoesntHave('monev', fn($q) => $q->where('submitter_user_id', $user->id));

        if ($isAdmin) {
            // Admin: semua kerjasama
        } elseif ($isTkksdLokus) {
            $base->whereHas('opds', fn($q) => $q->where('opd.id', $user->id_opd));
        } else {
            // Pemohon biasa
            $base->where('id_pemohon_0', $user->id);
        }

        $permohonans = $base->get(['id', 'uuid', 'label', 'nama_instansi', 'nomor_permohonan', 'tanggal_berakhir']);

        $selectedPermohonan = null;
        if ($request->has('permohonan')) {
            $sel = Permohonan::where('uuid', $request->permohonan)->first();
            // Validasi akses
            if ($sel) {
                $allowed = $isAdmin
                    || ($isTkksdLokus && $user->id_opd && $sel->opds()->where('opd.id', $user->id_opd)->exists())
                    || (!$isAdmin && !$isTkksdLokus && $sel->id_pemohon_0 == $user->id);
                if ($allowed) {
                    $selectedPermohonan = $sel;
                }
            }
        }

        $component = match (true) {
            $isAdmin => "$this->view/Create",
            $isTkksdLokus => "$this->view/Forms/Tkksd",
            default => "$this->view/Forms/Pemohon",
        };

        return Inertia::render($component, [
            'share' => array_merge($this->share, ['title' => 'Isi Form Monev']),
            'permohonans' => $permohonans,
            'selectedPermohonan' => $selectedPermohonan,
            'pemohon' => $pemohon,
            'submitterRole' => $isAdmin ? 'admin' : ($isTkksdLokus ? 'tkksd_lokus' : 'pemohon'),
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $pemohon = Pemohon::where('id_operator', $user->id)->first();
        $isAdmin = $user->can('monev.menu.admin');
        $isTkksdLokus = $user->hasRole('tkksd_lokus') && !$isAdmin;

        $rules = [
            'id_permohonan' => 'required|exists:permohonan,id',
            'tanggal_evaluasi' => 'required|date',
        ];

        if ($isAdmin) {
            // Form admin = form lama (tetap)
            $rules = array_merge($rules, [
                'kesesuaian_tujuan' => 'required',
                'ketepatan_waktu' => 'required',
                'kontribusi_mitra' => 'required',
                'tingkat_koordinasi' => 'required',
                'capaian_indikator' => 'required',
                'dampak_pelaksanaan' => 'required',
                'inovasi_manfaat' => 'required',
                'kelengkapan_dokumen' => 'required',
                'pelaporan_berkala' => 'required',
                'kendala_administrasi' => 'nullable|string',
                'relevansi_kebutuhan' => 'required',
                'rekomendasi_lanjutan' => 'required',
                'saran_rekomendasi' => 'nullable|string',
                'rating' => 'nullable|integer|min:1|max:5',
                'file_bukti' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            ]);
        } elseif ($isTkksdLokus) {
            $rules = array_merge($rules, [
                'tkl_kepatuhan_pks' => 'required|in:Patuh,Sebagian,Tidak',
                'tkl_koordinasi_mitra' => 'required|in:Sangat baik,Baik,Cukup,Kurang',
                'tkl_kesesuaian_anggaran' => 'required|in:Sesuai,Sebagian,Tidak',
                'tkl_temuan_pengawasan' => 'nullable|string',
                'tkl_rekomendasi_lokus' => 'required|in:Lanjutkan,Perbaiki,Hentikan',
                'tkl_catatan' => 'nullable|string',
            ]);
        } else {
            $rules = array_merge($rules, [
                'pmh_realisasi_kegiatan' => 'required|in:Terlaksana penuh,Sebagian,Tidak',
                'pmh_kesesuaian_output' => 'required|in:Ya,Sebagian,Tidak',
                'pmh_pemanfaatan_hasil' => 'nullable|string',
                'pmh_kendala_lapangan' => 'nullable|string',
                'pmh_keberlanjutan' => 'required|in:Perlu dilanjutkan,Cukup,Hentikan',
                'pmh_file_laporan' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            ]);
        }

        $validated = $request->validate($rules);

        // Cegah submitter yang sama monev permohonan yang sama lebih dari sekali
        $alreadySubmitted = Monev::where('id_permohonan', $validated['id_permohonan'])
            ->where('submitter_user_id', $user->id)
            ->exists();

        if ($alreadySubmitted) {
            return redirect()->back()->with('error', 'Anda sudah pernah submit monev untuk kerjasama ini.');
        }

        // Cek otorisasi berdasarkan role
        $permohonan = Permohonan::find($validated['id_permohonan']);
        if ($isTkksdLokus) {
            if (!$user->id_opd || !$permohonan->opds()->where('opd.id', $user->id_opd)->exists()) {
                abort(403, 'Anda hanya bisa monev kerjasama yang melibatkan OPD Anda.');
            }
        } elseif (!$isAdmin) {
            if ($permohonan->id_pemohon_0 != $user->id) {
                abort(403, 'Anda hanya bisa monev kerjasama Anda sendiri.');
            }
        }

        if ($isAdmin && $request->hasFile('file_bukti')) {
            $file = $request->file('file_bukti');
            $filename = 'monev_admin_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('monev', $filename, 'public');
            $validated['file_bukti'] = $path;
        }

        if (!$isAdmin && !$isTkksdLokus && $request->hasFile('pmh_file_laporan')) {
            $file = $request->file('pmh_file_laporan');
            $filename = 'monev_pmh_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('monev', $filename, 'public');
            $validated['pmh_file_laporan'] = $path;
        }

        // Pisahkan field detail (per role) dari payload monev inti.
        $detailKeys = match (true) {
            $isAdmin => Monev::ADMIN_DETAIL_KEYS,
            $isTkksdLokus => Monev::TKKSD_DETAIL_KEYS,
            default => Monev::PEMOHON_DETAIL_KEYS,
        };
        $detailPayload = array_intersect_key($validated, array_flip($detailKeys));
        $monevPayload = array_diff_key($validated, array_flip($detailKeys));

        $monevPayload['id_pemohon'] = $pemohon?->id;
        $monevPayload['status'] = Monev::STATUS_SUBMITTED;
        $monevPayload['tipe'] = $isAdmin ? 'manual' : 'reguler';
        $monevPayload['submitter_role'] = $isAdmin ? 'admin' : ($isTkksdLokus ? 'tkksd_lokus' : 'pemohon');
        $monevPayload['submitter_user_id'] = $user->id;

        $monev = \DB::transaction(function () use ($monevPayload, $detailPayload, $isAdmin, $isTkksdLokus) {
            $monev = Monev::create($monevPayload);

            $detailPayload['monev_id'] = $monev->id;
            if ($isAdmin) {
                MonevAdminDetail::create($detailPayload);
            } elseif ($isTkksdLokus) {
                MonevTkksdDetail::create($detailPayload);
            } else {
                MonevPemohonDetail::create($detailPayload);
            }

            return $monev;
        });

        // Notifikasi: hanya kirim ke admin (yang akan summarize hasil ketiga submitter).
        // Pemohon dan TKKSD Lokus monev mandiri-paralel, jadi tidak perlu saling notif.
        // Notifikasi tidak boleh menggagalkan flow utama — bungkus try/catch.
        try {
            $this->notifyAdminMonevSubmitted($monev);
        } catch (\Throwable $e) {
            \Log::warning('Gagal kirim notifikasi monev ke admin: ' . $e->getMessage(), [
                'monev_id' => $monev->id,
                'exception' => $e,
            ]);
        }

        return redirect()->route('monev.index', ['detail' => $monev->uuid])
            ->with('success', 'Form Monev berhasil disimpan.');
    }

    /**
     * Notifikasi ke admin saat ada monev baru disubmit (oleh siapa pun).
     */
    private function notifyAdminMonevSubmitted(Monev $monev): void
    {
        $monev->loadMissing(['permohonan', 'submitter']);
        $permohonan = $monev->permohonan;
        if (!$permohonan) return;

        $whatsapp = app(WhatsappService::class);
        $submitterName = $monev->submitter?->name ?? 'Sistem';
        $submitterRoleLabel = match ($monev->submitter_role) {
            'pemohon' => 'Pemohon',
            'tkksd_lokus' => 'TKKSD Lokus',
            'admin' => 'Admin',
            default => 'Pengirim',
        };

        $admins = \App\Models\User::adminNotificationRecipients()->get();

        $waMsg = \App\Services\NotificationTemplate::monevDariPemohonKeAdmin(
            $permohonan,
            $monev->kode_monev,
            $submitterRoleLabel,
            $submitterName
        );

        foreach ($admins as $admin) {
            Notifikasi::create([
                'id_user'       => $admin->id,
                'id_permohonan' => $permohonan->id,
                'from_user_id'  => Auth::id(),
                'type'          => 'monev_submitted',
                'title'         => "Monev Diterima dari {$submitterRoleLabel}",
                'message'       => "{$submitterRoleLabel} ({$submitterName}) mengirim Monev {$monev->kode_monev} untuk kerja sama \"{$permohonan->label}\". Mohon ditinjau pada dashboard.",
                'data'          => json_encode(['monev_uuid' => $monev->uuid]),
                'is_read'       => false,
            ]);

            if ($admin->phone) {
                try { $whatsapp->sendToAdmin($admin->phone, $waMsg); } catch (\Throwable $e) { \Log::error("Failed sending Monev notification to admin #{$admin->id}: " . $e->getMessage()); }
            }
        }
    }

    /**
     * TKKSD Lokus menyetujui hasil evaluasi pemohon (Req 11.5, 11.6).
     */
    public function tkksdReview(Request $request, string $uuid)
    {
        $user = Auth::user();

        if (!$user->hasRole('tkksd_lokus')) {
            abort(403, 'Hanya TKKSD Lokus yang dapat menyetujui evaluasi.');
        }

        $monev = Monev::where('uuid', $uuid)->firstOrFail();

        // Pastikan TKKSD Lokus terhubung ke OPD yang terlibat di kerjasama ini
        $opdIds = $monev->permohonan?->opds()->pluck('opd.id')->toArray() ?? [];
        if (!in_array($user->id_opd, $opdIds)) {
            abort(403, 'Anda hanya dapat menyetujui monev untuk kerjasama yang melibatkan OPD Anda.');
        }

        $validated = $request->validate([
            'tkksd_catatan' => 'nullable|string',
            'is_approved' => 'required|boolean',
        ]);

        $monev->update([
            'id_tkksd_lokus' => $user->id,
            'tkksd_approved_at' => $validated['is_approved'] ? now() : null,
            'tkksd_catatan' => $validated['tkksd_catatan'] ?? null,
            'status' => $validated['is_approved'] ? Monev::STATUS_REVIEWED : Monev::STATUS_SUBMITTED,
        ]);

        // Notif ke admin
        if ($validated['is_approved']) {
            $admins = \App\Models\User::adminNotificationRecipients()->pluck('id');

            foreach ($admins as $adminId) {
                Notifikasi::create([
                    'id_user' => $adminId,
                    'id_permohonan' => $monev->id_permohonan,
                    'from_user_id' => $user->id,
                    'type' => 'monev_tkksd_approved',
                    'title' => 'Monev Disetujui TKKSD Lokus',
                    'message' => "Hasil evaluasi pemohon untuk monev {$monev->kode_monev} telah disetujui TKKSD Lokus. Silakan buat monev final.",
                    'data' => json_encode(['monev_uuid' => $monev->uuid]),
                    'is_read' => false,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Persetujuan TKKSD Lokus berhasil disimpan.');
    }

    /**
     * Admin: input monev manual untuk kerjasama yang tidak melalui sistem (Req 16).
     */
    public function storeManual(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'id_permohonan' => 'nullable|exists:permohonan,id',
            'tanggal_evaluasi' => 'required|date',
            'kesesuaian_tujuan' => 'required',
            'ketepatan_waktu' => 'required',
            'kontribusi_mitra' => 'required',
            'tingkat_koordinasi' => 'required',
            'capaian_indikator' => 'required',
            'dampak_pelaksanaan' => 'required',
            'inovasi_manfaat' => 'required',
            'kelengkapan_dokumen' => 'required',
            'pelaporan_berkala' => 'required',
            'kendala_administrasi' => 'nullable|string',
            'relevansi_kebutuhan' => 'required',
            'rekomendasi_lanjutan' => 'required',
            'saran_rekomendasi' => 'nullable|string',
            'rating' => 'required|integer|min:1|max:5',
            'catatan_admin' => 'nullable|string',
        ]);

        $detailPayload = array_intersect_key($validated, array_flip(Monev::ADMIN_DETAIL_KEYS));
        $monevPayload = array_diff_key($validated, array_flip(Monev::ADMIN_DETAIL_KEYS));
        $monevPayload['tipe'] = 'manual';
        $monevPayload['status'] = Monev::STATUS_REVIEWED;
        $monevPayload['reviewed_by'] = Auth::id();
        $monevPayload['reviewed_at'] = now();
        $monevPayload['submitter_role'] = 'admin';
        $monevPayload['submitter_user_id'] = Auth::id();

        $monev = \DB::transaction(function () use ($monevPayload, $detailPayload) {
            $monev = Monev::create($monevPayload);
            $detailPayload['monev_id'] = $monev->id;
            MonevAdminDetail::create($detailPayload);
            return $monev;
        });

        return redirect()->route('monev.index', ['detail' => $monev->uuid])
            ->with('success', 'Monev manual berhasil disimpan.');
    }

    public function show(string $uuid)
    {
        $user = Auth::user();
        $isAdmin = $user->can('monev.menu.admin');

        $monev = Monev::with([
                'permohonan.kategori', 'permohonan.pemohon', 'permohonan.opds',
                'pemohon', 'reviewer', 'submitter',
                'adminDetail', 'pemohonDetail', 'tkksdDetail',
            ])
            ->where('uuid', $uuid)
            ->firstOrFail();

        if (!$isAdmin) {
            $pemohon = Pemohon::where('id_operator', $user->id)->first();
            $isTkksdLokus = $user->hasRole('tkksd_lokus');

            if ($isTkksdLokus) {
                $hasAccess = ($monev->submitter_user_id === $user->id) ||
                    ($user->id_opd && $monev->permohonan
                        && $monev->permohonan->opds()->where('opd.id', $user->id_opd)->exists());
            } else {
                // Pemohon hanya boleh melihat monev miliknya sendiri.
                $hasAccess = ($monev->submitter_user_id === $user->id) ||
                    ($pemohon && $monev->id_pemohon === $pemohon->id && is_null($monev->submitter_user_id));
            }

            if (!$hasAccess) {
                abort(403, 'Akses ditolak.');
            }
        }

        // Saat XHR/JSON: kembalikan data mentah untuk modal
        if (request()->wantsJson()) {
            return response()->json($monev);
        }

        // Tidak ada halaman Show terpisah — selalu lempar ke Index dengan query detail
        return redirect()->route('monev.index', ['detail' => $monev->uuid]);
    }

    public function review(Request $request, string $uuid)
    {
        $monev = Monev::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'catatan_admin' => 'nullable|string',
        ]);

        $monev->update([
            'status' => Monev::STATUS_REVIEWED,
            'catatan_admin' => $validated['catatan_admin'],
            'reviewed_at' => now(),
            'reviewed_by' => Auth::id(),
        ]);

        // Auto-transition status permohonan ke SELESAI ketika monev final di-approve admin
        if ($monev->permohonan && $monev->permohonan->status == Permohonan::STATUS_PELAKSANAAN) {
            $monev->permohonan->update(['status' => Permohonan::STATUS_SELESAI]);
        }

        return redirect()->back()->with('success', 'Monev berhasil direview. Status kerjasama diubah ke Selesai.');
    }

    public function notifyPemohon(string $uuid, WhatsappService $whatsapp)
    {
        $monev = Monev::with(['permohonan.pemohon', 'permohonan.operator', 'adminDetail'])
            ->where('uuid', $uuid)
            ->firstOrFail();
        $permohonan = $monev->permohonan;

        if (!$permohonan || !$permohonan->id_pemohon_0) {
            return redirect()->back()->with('error', 'Pemohon untuk Monev ini tidak ditemukan.');
        }

        $followUpStatus = match ($monev->rekomendasi_lanjutan) {
            'Dilanjutkan' => 'Layak dilanjutkan',
            'Diperluas' => 'Layak diperluas',
            'Dihentikan' => 'Tidak direkomendasikan dilanjutkan',
            default => 'Rekomendasi tindak lanjut tersedia',
        };

        $pemohonUser = $permohonan->operator;
        $pemohonName = $permohonan->pemohon?->name ?: ($pemohonUser?->name ?: 'Pemohon');

        $payload = \App\Services\NotificationTemplate::monevHasilKePemohon($pemohonName, $permohonan, $followUpStatus);

        Notifikasi::create([
            'id_user'       => $permohonan->id_pemohon_0,
            'id_permohonan' => $permohonan->id,
            'from_user_id'  => Auth::id(),
            'type'          => 'monev',
            'title'         => $payload['system']['title'],
            'message'       => $payload['system']['message'],
            'data'          => [
                'monev_uuid' => $monev->uuid,
                'permohonan_uuid' => $permohonan->uuid,
                'rekomendasi_lanjutan' => $monev->rekomendasi_lanjutan,
            ],
            'is_read'       => false,
        ]);

        $targetPhone = $pemohonUser?->phone ?: $permohonan->pemohon?->phone;
        if (!$targetPhone) {
            return redirect()->back()->withErrors([
                'notification' => 'Notifikasi aplikasi dibuat, tetapi nomor WhatsApp pemohon tidak ditemukan.',
            ]);
        }

        if (!$whatsapp->sendMessage($targetPhone, $payload['wa'])) {
            return redirect()->back()->withErrors([
                'notification' => 'Notifikasi aplikasi dibuat, tetapi pesan WhatsApp gagal dikirim. Periksa gateway WA dan nomor pemohon.',
            ]);
        }

        return redirect()->back()->with('success', 'Notifikasi hasil Monev dan pesan WhatsApp dikirim ke pemohon.');
    }

    public function export(Request $request)
    {
        $monevs = Monev::with(['permohonan.kategori', 'adminDetail', 'pemohonDetail', 'tkksdDetail'])
            ->latest()
            ->get();

        $filename = 'monev_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($monevs) {
            $file = fopen('php://output', 'w');

            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [
                'Kode Monev',
                'Kerjasama',
                'Instansi',
                'Kategori',
                'Tanggal Evaluasi',
                'Kesesuaian Tujuan',
                'Ketepatan Waktu',
                'Kontribusi Mitra',
                'Tingkat Koordinasi',
                'Capaian Indikator',
                'Dampak Pelaksanaan',
                'Inovasi Manfaat',
                'Kelengkapan Dokumen',
                'Pelaporan Berkala',
                'Relevansi Kebutuhan',
                'Rekomendasi Lanjutan',
                'Kendala Administrasi',
                'Saran Rekomendasi',
            ]);

            foreach ($monevs as $monev) {
                fputcsv($file, [
                    $monev->kode_monev,
                    $monev->permohonan?->label ?? '-',
                    $monev->permohonan?->nama_instansi ?? '-',
                    $monev->permohonan?->kategori?->label ?? '-',
                    $monev->tanggal_evaluasi?->format('d/m/Y'),
                    $monev->kesesuaian_tujuan,
                    $monev->ketepatan_waktu,
                    $monev->kontribusi_mitra,
                    $monev->tingkat_koordinasi,
                    $monev->capaian_indikator,
                    $monev->dampak_pelaksanaan,
                    $monev->inovasi_manfaat,
                    $monev->kelengkapan_dokumen,
                    $monev->pelaporan_berkala,
                    $monev->relevansi_kebutuhan,
                    $monev->rekomendasi_lanjutan,
                    $monev->kendala_administrasi,
                    $monev->saran_rekomendasi,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
