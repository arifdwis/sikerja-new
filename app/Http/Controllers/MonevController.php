<?php

namespace App\Http\Controllers;

use App\Models\Monev;
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
                    ])->values(),
                    // Ringkasan agregat untuk kolom-kolom listing
                    'monev_count' => $monevs->count(),
                    'submitter_roles' => $rolesPresent,
                    'latest_tanggal_evaluasi' => $latest?->tanggal_evaluasi?->toDateString(),
                    'latest_rekomendasi' => $latest?->rekomendasi_lanjutan,
                    'kode_monev' => $monevs->first()?->kode_monev, // representatif untuk pencarian
                ];
            });

            $pendingPermohonans = Permohonan::with(['kategori', 'operator'])
                ->whereIn('status', [Permohonan::STATUS_PELAKSANAAN, Permohonan::STATUS_SELESAI])
                ->whereNotNull('tanggal_berakhir')
                ->where('tanggal_berakhir', '<=', now())
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
        $query = Monev::with(['permohonan.kategori', 'permohonan.pemohon', 'permohonan.opds', 'pemohon', 'reviewer', 'submitter'])
            ->latest();

        if ($isTkksdLokus) {
            $query->where('submitter_user_id', $user->id);
        } else {
            // Pemohon: monev yang dia submit, atau monev di permohonan miliknya (back-compat data lama)
            $query->where(function ($q) use ($user) {
                $q->where('submitter_user_id', $user->id)
                    ->orWhereHas('permohonan', fn($q2) => $q2->where('id_pemohon_0', $user->id));
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
            ->whereIn('status', [Permohonan::STATUS_PELAKSANAAN, Permohonan::STATUS_SELESAI])
            ->whereNotNull('tanggal_berakhir')
            ->where('tanggal_berakhir', '<=', now());

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
        $base = Permohonan::whereIn('status', [Permohonan::STATUS_PELAKSANAAN, Permohonan::STATUS_SELESAI])
            ->whereNotNull('tanggal_berakhir')
            ->where('tanggal_berakhir', '<=', now())
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

        return Inertia::render("$this->view/Create", [
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

        $validated = $request->validate([
            'id_permohonan' => 'required|exists:permohonan,id',
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
            'rating' => 'nullable|integer|min:1|max:5',
            'file_bukti' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

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

        if ($request->hasFile('file_bukti')) {
            $file = $request->file('file_bukti');
            $filename = 'monev_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('monev', $filename, 'public');
            $validated['file_bukti'] = $path;
        }

        $validated['id_pemohon'] = $pemohon?->id;
        $validated['status'] = Monev::STATUS_SUBMITTED;
        $validated['tipe'] = $isAdmin ? 'manual' : 'reguler';
        $validated['submitter_role'] = $isAdmin ? 'admin' : ($isTkksdLokus ? 'tkksd_lokus' : 'pemohon');
        $validated['submitter_user_id'] = $user->id;

        $monev = Monev::create($validated);

        // Notifikasi: hanya kirim ke admin (yang akan summarize hasil ketiga submitter).
        // Pemohon dan TKKSD Lokus monev mandiri-paralel, jadi tidak perlu saling notif.
        $this->notifyAdminMonevSubmitted($monev);

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
                try { $whatsapp->sendToAdmin($admin->phone, $waMsg); } catch (\Throwable $e) {}
            }
        }
    }

    /**
     * Kirim notifikasi monev ke 3 pihak: pemohon, admin, dan TKKSD Lokus
     * (sesuai Req: monev terlibat 3 stakeholder).
     */
    private function dispatchMonevSubmittedNotifications(Monev $monev): void
    {
        $monev->loadMissing(['permohonan.opds', 'permohonan.operator']);
        $permohonan = $monev->permohonan;

        if (!$permohonan) {
            return;
        }

        $whatsapp = app(WhatsappService::class);
        $kodeMonev = $monev->kode_monev;

        // 1) Konfirmasi ke pemohon (system + WA) — pakai template formal
        if ($permohonan->id_pemohon_0) {
            $pemohonUser = $permohonan->operator;
            $namaPemohon = $permohonan->pemohon?->name ?: ($pemohonUser?->name ?: 'Pemohon');

            $payload = \App\Services\NotificationTemplate::statusUpdate(
                $namaPemohon,
                $permohonan,
                'Form Monev Terkirim',
                "Form Monitoring & Evaluasi {$kodeMonev} telah berhasil dikirim. Saat ini menunggu persetujuan TKKSD Lokus dan admin."
            );

            Notifikasi::create([
                'id_user'       => $permohonan->id_pemohon_0,
                'id_permohonan' => $permohonan->id,
                'from_user_id'  => Auth::id(),
                'type'          => 'monev_submitted',
                'title'         => 'Form Monev Terkirim',
                'message'       => $payload['system']['message'],
                'data'          => json_encode(['monev_uuid' => $monev->uuid]),
                'is_read'       => false,
            ]);

            if ($pemohonUser?->phone) {
                try { $whatsapp->sendMessage($pemohonUser->phone, $payload['wa']); } catch (\Throwable $e) {}
            }
        }

        // 2) Notif ke admin (system + WA, sesuai whitelist)
        $admins = \App\Models\User::adminNotificationRecipients()->get();

        $waAdmin = \App\Services\NotificationTemplate::monevDariPemohonKeAdmin(
            $permohonan,
            $kodeMonev,
            'Pemohon',
            $permohonan->operator?->name ?? '-'
        );

        foreach ($admins as $admin) {
            Notifikasi::create([
                'id_user'       => $admin->id,
                'id_permohonan' => $permohonan->id,
                'from_user_id'  => Auth::id(),
                'type'          => 'monev_submitted_admin',
                'title'         => 'Monev Baru Menunggu Review',
                'message'       => "Pemohon mengirim form Monev {$kodeMonev} untuk kerja sama \"{$permohonan->label}\". Menunggu persetujuan TKKSD Lokus.",
                'data'          => json_encode(['monev_uuid' => $monev->uuid]),
                'is_read'       => false,
            ]);

            if ($admin->phone) {
                try { $whatsapp->sendToAdmin($admin->phone, $waAdmin); } catch (\Throwable $e) {}
            }
        }

        // 3) Notif ke TKKSD Lokus untuk review (system + WA)
        $opdIds = $permohonan->opds->pluck('id')->toArray();
        if (!empty($opdIds)) {
            $tkksdLokusUsers = \App\Models\User::whereHas('roles', fn($q) => $q->where('slug', 'tkksd_lokus'))
                ->whereIn('id_opd', $opdIds)
                ->with('opd')
                ->get();

            foreach ($tkksdLokusUsers as $u) {
                $namaOpd = $u->opd?->nama ?? $u->name ?? 'TKKSD Lokus';
                $payload = \App\Services\NotificationTemplate::reviewRequestKeTkksdLokus($namaOpd, $permohonan, $kodeMonev);

                Notifikasi::create([
                    'id_user'       => $u->id,
                    'id_permohonan' => $permohonan->id,
                    'from_user_id'  => Auth::id(),
                    'type'          => 'monev_review_request',
                    'title'         => $payload['system']['title'],
                    'message'       => $payload['system']['message'],
                    'data'          => json_encode(['monev_uuid' => $monev->uuid]),
                    'is_read'       => false,
                ]);

                if ($u->phone) {
                    try { $whatsapp->sendMessage($u->phone, $payload['wa']); } catch (\Throwable $e) {}
                }
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

        $validated['tipe'] = 'manual';
        $validated['status'] = Monev::STATUS_REVIEWED;
        $validated['reviewed_by'] = Auth::id();
        $validated['reviewed_at'] = now();

        $monev = Monev::create($validated);

        return redirect()->route('monev.index', ['detail' => $monev->uuid])
            ->with('success', 'Monev manual berhasil disimpan.');
    }

    /**
     * Notif ke TKKSD Lokus dari OPD yang terlibat untuk review evaluasi pemohon (Req 11.5).
     * @deprecated Diganti dispatchMonevSubmittedNotifications() yang juga kirim ke pemohon & admin.
     */
    private function notifTkksdLokusForReview(Monev $monev): void
    {
        $this->dispatchMonevSubmittedNotifications($monev);
    }

    public function show(string $uuid)
    {
        $user = Auth::user();
        $isAdmin = $user->can('monev.menu.admin');

        $monev = Monev::with(['permohonan.kategori', 'permohonan.pemohon', 'permohonan.opds', 'pemohon', 'reviewer', 'submitter'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        if (!$isAdmin) {
            $pemohon = Pemohon::where('id_operator', $user->id)->first();
            $isTkksdLokus = $user->hasRole('tkksd_lokus');

            $hasAccess = ($pemohon && $monev->id_pemohon === $pemohon->id) ||
                ($monev->permohonan && $monev->permohonan->id_pemohon_0 === $user->id) ||
                ($monev->submitter_user_id === $user->id) ||
                ($isTkksdLokus && $user->id_opd && $monev->permohonan
                    && $monev->permohonan->opds()->where('opd.id', $user->id_opd)->exists());

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
        $monev = Monev::with(['permohonan.pemohon', 'permohonan.operator'])
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
        $monevs = Monev::with(['permohonan.kategori'])
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
