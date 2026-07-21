<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use App\Models\User;
use App\Models\Kategori;
use App\Models\Provinsi;
use App\Models\Kota;
use App\Models\Pemohon;
use App\Models\Corporate;
use App\Models\PermohonanFile;
use App\Models\PermohonanHistori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Services\WhatsappService;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class PermohonanController extends Controller implements HasMiddleware
{
    protected $title;
    protected $prefix;
    protected $view;
    protected $data;
    protected $share;

    public function __construct(Permohonan $data)
    {
        $this->title = 'Permohonan Kerjasama';
        $this->view = 'Backend/Permohonan';
        $this->prefix = 'permohonan';
        $this->data = $data;

        $this->share = [
            'title' => $this->title,
            'view' => $this->view,
            'prefix' => $this->prefix
        ];
    }

    public static function middleware(): array
    {
        // Define permission mapping
        return [
            // Index bisa diakses oleh berbagai role dengan permission dasar index
            // Role Verifikator/Pimpinan juga harus punya permission ini untuk lihat list
            new Middleware('can:permohonan.index', only: ['index']),

            new Middleware('can:permohonan.create', only: ['create', 'store']),
            new Middleware('can:permohonan.show', only: ['show']),

            // Edit & Update butuh permission edit
            new Middleware('can:permohonan.edit', only: ['edit', 'update']),

            new Middleware('can:permohonan.destroy', only: ['destroy']),

            // Update status khusus role tertentu (verifikator/pimpinan)
            new Middleware('can:permohonan.status', only: ['updateStatus']),
        ];
    }

    /**
     * Display a listing of permohonan.
     */
    public function index(Request $request)
    {
        $query = $this->data::with([
            'kategori',
            'operator',
            'provinsi',
            'kota',
            'files',
            'opds',
            'penjadwalans' => function ($q) {
                $q->latest();
            }
        ]);
        $routeName = Route::currentRouteName();
        $pageTitle = $this->title;

        // Filter based on Route Name (untuk menu Sidebar)
        if ($routeName === 'validasi.index') {
            // Validasi: status 0 (Permohonan Baru menunggu validasi admin)
            $query->where('status', Permohonan::STATUS_PERMOHONAN);
            $pageTitle = 'Validasi Permohonan';
        } elseif ($routeName === 'pembahasan.index') {
            // Pembahasan: status 1 (Dalam Pembahasan TKKSD)
            $query->where('status', Permohonan::STATUS_PEMBAHASAN);
            $pageTitle = 'Pembahasan Permohonan';
        } elseif ($routeName === 'persetujuan.index') {
            // Persetujuan: status 2 saja (admin review jadwal yang diajukan pemohon)
            $query->where('status', Permohonan::STATUS_PENJADWALAN);
            $pageTitle = 'Persetujuan Jadwal';
        } elseif ($routeName === 'penandatanganan.index') {
            // Penandatanganan: tahap upload PKS sampai validasi dokumen TTD
            // 3 = Jadwal Disetujui, menunggu pemohon upload PKS
            // 4 = PKS terupload, menunggu hari penandatanganan
            // 5 = Pasca-tandatangan, admin validasi dokumen + approve PKS final
            $query->whereIn('status', [
                Permohonan::STATUS_JADWAL_DISETUJUI,
                Permohonan::STATUS_MENUNGGU_TANDATANGAN,
                Permohonan::STATUS_PASCA_TANDATANGAN,
            ]);
            $pageTitle = 'Penandatanganan Kerjasama';
        } elseif ($routeName === 'pelaksanaan.index') {
            // Pelaksanaan: status 6 (kerjasama aktif berjalan)
            $query->where('status', Permohonan::STATUS_PELAKSANAAN);
            $pageTitle = 'Pelaksanaan Kerjasama';
        } elseif ($routeName === 'permohonan.selesai') {
            // Permohonan Selesai: status 7 (monev final dibuat, kerjasama selesai)
            $query->where('status', Permohonan::STATUS_SELESAI);
            $pageTitle = 'Permohonan Selesai';
        } else {
            // Default permohonan.index ("Permohonan Saya"):
            // Pemohon hanya melihat alur aktif. Status final dipindah ke menu Riwayat.
            // tampil di menu Riwayat agar tidak campur dengan permohonan yang masih berproses.
            if (Auth::user()->hasRole('pemohon')
                && !Auth::user()->hasRole(['administrator', 'superadmin', 'tkksd', 'tkksd_lokus'])) {
                $query->whereNotIn('status', [
                    Permohonan::STATUS_PELAKSANAAN,
                    Permohonan::STATUS_SELESAI,
                    Permohonan::STATUS_DICABUT,
                    Permohonan::STATUS_DITOLAK,
                ]);
            }
        }

        // Filter by user role (Ownership Scope)
        // Pemohon hanya melihat data miliknya sendiri
        if (Auth::user()->hasRole('pemohon')) {
            $query->where('id_pemohon_0', Auth::id());
        }

        // TKKSD Lokus hanya melihat kerjasama yang melibatkan OPD-nya
        if (Auth::user()->hasRole('tkksd_lokus') && !Auth::user()->hasRole(['administrator', 'superadmin'])) {
            $opdId = Auth::user()->id_opd;
            if ($opdId) {
                $query->whereHas('opds', fn($q) => $q->where('opd.id', $opdId));
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        // Filter manually requested status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by tahun
        if ($request->has('tahun') && $request->tahun) {
            $query->whereYear('created_at', $request->tahun);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('label', 'like', "%{$search}%")
                    ->orWhere('nama_instansi', 'like', "%{$search}%")
                    ->orWhere('nomor_permohonan', 'like', "%{$search}%")
                    ->orWhere('kode', 'like', "%{$search}%");
            });
        }

        $permohonan = $query->latest()->paginate(15)->withQueryString();

        $statusLabels = Permohonan::statusLabels();
        $kategoris = Kategori::all();
        $provinsis = Provinsi::all();

        // Data needed for Create Modal
        $user = Auth::user();
        $pemohon = null;
        $corporate = null;

        if ($user->hasRole('pemohon')) {
            $pemohon = Pemohon::where('id_operator', $user->id)->first();
            $corporate = Corporate::where('id_operator', $user->id)->first();
        }

        // Load all pemohon for PPKSD-2 dropdown
        $pemohonanList = Pemohon::orderBy('name')->get(['id', 'name', 'jabatan', 'unit_kerja']);

        // Daftar OPD aktif untuk multi-select & auto-fill OPD jika user terhubung via SSO
        $opds = \App\Models\Opd::active()->orderBy('nama')->get();
        $userOpd = $user->id_opd ? \App\Models\Opd::find($user->id_opd) : null;

        return Inertia::render("$this->view/Index", [
            'permohonan' => $permohonan,
            'statusLabels' => $statusLabels,
            'kategoris' => $kategoris,
            'provinsis' => $provinsis,
            'opds' => $opds,
            'userOpd' => $userOpd,
            'pemohon' => $pemohon,
            'corporate' => $corporate,
            'pemohonanList' => $pemohonanList,
            'filters' => $request->only(['status', 'tahun', 'search', 'detail']),
            'pageTitle' => $pageTitle,
            'currentRoute' => $routeName,
            'share' => $this->share,
        ]);
    }

    /**
     * Display a history of permohonan.
     */
    public function riwayat(Request $request)
    {
        $pageTitle = 'Riwayat Permohonan';

        // Riwayat menampilkan semua kerjasama yang sudah keluar dari alur aktif:
        // - 6 = Pelaksanaan (sedang terlaksana, sebelum tanggal_berakhir)
        // - 7 = Selesai (tanggal_berakhir kerjasama sudah lewat)
        // - 8 = Dicabut
        // - 9 = Ditolak (revisi)
        $finalStatuses = [
            Permohonan::STATUS_PELAKSANAAN,
            Permohonan::STATUS_SELESAI,
            Permohonan::STATUS_DICABUT,
            Permohonan::STATUS_DITOLAK,
        ];

        // Build one history query for all final statuses. The old status tabs were
        // removed from the page, so a stale ?tab= query must not hide records.
        $base = $this->data::query()->whereIn('status', $finalStatuses);

        // Ownership Check (Strictly for Pemohon)
        if (Auth::user()->hasRole('pemohon')) {
            $base->where('id_pemohon_0', Auth::id());
        }

        $query = $base->with(['kategori', 'operator', 'kota']);

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('label', 'like', "%{$search}%")
                    ->orWhere('nama_instansi', 'like', "%{$search}%")
                    ->orWhere('nomor_permohonan', 'like', "%{$search}%");
            });
        }

        $permohonan = $query->latest()->paginate(15)->withQueryString();
        $statusLabels = Permohonan::statusLabels();

        return Inertia::render("Backend/Riwayat/Index", [
            'permohonan'  => $permohonan,
            'statusLabels' => $statusLabels,
            'filters'     => $request->only(['search']),
            'share' => [
                'title'  => $pageTitle,
                'prefix' => 'riwayat',
            ],
        ]);
    }

    /**
     * Show the form for creating a new permohonan.
     */
    public function create()
    {
        $user = Auth::user();
        if ($this->isPrivilegedAdmin($user)) {
            abort(403, 'Akun admin/superadmin tidak dapat mengajukan permohonan kerjasama.');
        }

        $kategoris = Kategori::all();
        $provinsis = Provinsi::all();
        $opds = \App\Models\Opd::active()->orderBy('nama')->get();

        // Load pemohon and corporate data for auto-fill
        $pemohon = null;
        $corporate = null;
        $pemohonanList = [];

        if ($user->hasRole('pemohon')) {
            $pemohon = Pemohon::where('id_operator', $user->id)->with('kotaRef')->first();
            $corporate = Corporate::where('id_operator', $user->id)->with('kotaRef')->first();
        }

        // Load all pemohon for PPKSD-2 dropdown
        $pemohonanList = Pemohon::orderBy('name')->get(['id', 'name', 'jabatan', 'unit_kerja']);

        // Auto-fill OPD jika user terhubung ke OPD via SSO (id_opd di users)
        $userOpd = $user->id_opd ? \App\Models\Opd::find($user->id_opd) : null;

        return Inertia::render("$this->view/Create", [
            'kategoris' => $kategoris,
            'provinsis' => $provinsis,
            'opds' => $opds,
            'userOpd' => $userOpd,
            'pemohon' => $pemohon,
            'corporate' => $corporate,
            'pemohonanList' => $pemohonanList,
            'share' => $this->share,
        ]);
    }

    /**
     * Store a newly created permohonan.
     */
    public function store(Request $request)
    {
        if ($this->isPrivilegedAdmin(Auth::user())) {
            return redirect()->back()->with('error', 'Akun admin/superadmin tidak dapat mengajukan permohonan kerjasama.');
        }

        $validated = $request->validate([
            'id_kategori' => 'required|exists:kategori,id',
            'label' => 'required|string|max:255',
            'nama_instansi' => 'required|string|max:255',
            'id_provinsi' => 'nullable|exists:master_provinces,id',
            'id_kota' => 'nullable|exists:master_cities,id',
            'kode_pos' => 'nullable|string|max:10',
            'alamat' => 'nullable|string',
            'email' => 'nullable|email|max:255',
            'telepon' => 'nullable|string|max:30',
            'website' => 'nullable|string|max:255',
            'id_pemohon_1' => 'nullable|exists:pemohon,id',
            'latar_belakang' => 'required|string',
            'maksud_tujuan' => 'required|string',
            'lokasi_kerjasama' => 'nullable|string',
            'ruang_lingkup' => 'required|string',
            'jangka_waktu' => 'nullable|string|max:255',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_berakhir' => 'nullable|date|after_or_equal:tanggal_mulai',
            'manfaat' => 'nullable|string',
            'analisis_dampak' => 'nullable|string',
            'pembiayaan' => 'nullable|string',
            // OPD multiple - Req 12
            'opd_ids' => 'nullable|array',
            'opd_ids.*' => 'exists:opd,id',
        ]);

        // Auto-fill OPD jika user terhubung ke OPD via SSO dan tidak ada opd_ids di request
        $opdIds = $validated['opd_ids'] ?? [];
        $user = Auth::user();
        if (empty($opdIds) && $user->id_opd) {
            $opdIds = [$user->id_opd];
        }
        unset($validated['opd_ids']);

        // Auto-compute jangka_waktu dari tanggal_mulai & tanggal_berakhir (safeguard server-side)
        $validated['jangka_waktu'] = $this->computeJangkaWaktu(
            $validated['tanggal_mulai'] ?? null,
            $validated['tanggal_berakhir'] ?? null
        ) ?: ($validated['jangka_waktu'] ?? null);

        $validated['kode'] = 'PKS-' . strtoupper(Str::random(8));
        $validated['nomor_permohonan'] = 'REQ/' . date('Y') . '/' . str_pad(Permohonan::whereYear('created_at', date('Y'))->count() + 1, 4, '0', STR_PAD_LEFT);
        $validated['id_pemohon_0'] = Auth::id();
        $validated['status'] = Permohonan::STATUS_PERMOHONAN;

        $permohonan = $this->data::create($validated);

        // Sync OPD ke pivot
        if (!empty($opdIds)) {
            $permohonan->opds()->sync($opdIds);
        }

        PermohonanHistori::create([
            'id_permohonan' => $permohonan->id,
            'id_operator' => Auth::id(),
            'deskripsi' => 'Permohonan kerjasama dibuat.',
        ]);

        return redirect()->route("$this->prefix.index", ['detail' => $permohonan->uuid])
            ->with('success', 'Permohonan berhasil dibuat.');
    }

    /**
     * Admin & superadmin berperan sebagai reviewer/manajer alur,
     * bukan pihak pengaju permohonan.
     */
    private function isPrivilegedAdmin(?User $user): bool
    {
        if (!$user) return false;
        return $user->hasRole(['administrator', 'superadmin', 'admin']);
    }

    /**
     * Display the specified permohonan.
     */
    public function show(string $uuid)
    {
        $permohonan = $this->data::with([
            'kategori',
            'operator',
            'provinsi',
            'kota',
            'pemohon1',
            'pemohon2',
            'files',
            'opds',
            'pksFiles.uploader',
            'ttdFiles.uploader',
            'ttdFiles.validator',
            'historis.operator',
            'penjadwalans' => function ($q) {
                $q->latest()->with('approver');
            }
        ])->uuid($uuid)->firstOrFail();

        // Ownership check can be handled by Policy, or kept here if Policy not set up
        if (Auth::user()->hasRole('pemohon') && $permohonan->id_pemohon_0 != Auth::id()) {
            abort(403, 'Unauthorized access to this permohonan');
        }

        // TKKSD Lokus: hanya boleh lihat kerjasama yang melibatkan OPD-nya
        if (Auth::user()->hasRole('tkksd_lokus') && !Auth::user()->hasRole(['administrator', 'superadmin'])) {
            $opdId = Auth::user()->id_opd;
            $opdIds = $permohonan->opds->pluck('id')->toArray();
            if (!$opdId || !in_array($opdId, $opdIds)) {
                abort(403, 'Anda hanya dapat melihat kerjasama yang melibatkan OPD Anda.');
            }
        }

        if (request()->wantsJson()) {
            return response()->json($permohonan);
        }

        return redirect()->route("$this->prefix.index", ['detail' => $permohonan->uuid]);
    }

    /**
     * Show the form for editing the specified permohonan.
     */
    public function edit(string $uuid)
    {
        $permohonan = $this->data::uuid($uuid)->firstOrFail();

        if (Auth::user()->hasRole('pemohon') && $permohonan->id_pemohon_0 != Auth::id()) {
            abort(403);
        }

        // Izinkan edit jika status: Permohonan Baru (0) atau Ditolak (9 - revisi)
        $editableStatuses = [Permohonan::STATUS_PERMOHONAN, Permohonan::STATUS_DITOLAK];
        if (!in_array($permohonan->status, $editableStatuses)) {
            return redirect()->route("$this->prefix.show", $permohonan->uuid)
                ->with('error', 'Permohonan tidak dapat diedit karena sudah diproses.');
        }

        $kategoris = Kategori::all();
        $provinsis = Provinsi::all();
        $kotas = Kota::where('province_id', $permohonan->id_provinsi)->get();
        $opds = \App\Models\Opd::active()->orderBy('nama')->get();
        $selectedOpdIds = $permohonan->opds()->pluck('opd.id')->toArray();
        // Untuk dropdown PPKSD-2 (Pihak Kedua) saat edit/revisi
        $pemohonanList = Pemohon::orderBy('name')->get(['id', 'name', 'jabatan', 'unit_kerja']);

        return Inertia::render("$this->view/Edit", [
            'permohonan' => $permohonan,
            'kategoris' => $kategoris,
            'provinsis' => $provinsis,
            'kotas' => $kotas,
            'opds' => $opds,
            'selectedOpdIds' => $selectedOpdIds,
            'pemohonanList' => $pemohonanList,
            'share' => $this->share,
        ]);
    }

    /**
     * Update the specified permohonan.
     */
    public function update(Request $request, string $uuid)
    {
        $permohonan = $this->data::uuid($uuid)->firstOrFail();

        if (Auth::user()->hasRole('pemohon') && $permohonan->id_pemohon_0 != Auth::id()) {
            abort(403);
        }

        // Izinkan edit jika status: Permohonan Baru (0) atau Ditolak (9 - revisi)
        $editableStatuses = [Permohonan::STATUS_PERMOHONAN, Permohonan::STATUS_DITOLAK];
        if (!in_array($permohonan->status, $editableStatuses)) {
            return redirect()->route("$this->prefix.show", $permohonan->uuid)
                ->with('error', 'Permohonan tidak dapat diedit karena sudah diproses.');
        }

        $wasRejected = $permohonan->status == Permohonan::STATUS_DITOLAK;

        $validated = $request->validate([
            'id_kategori' => 'required|exists:kategori,id',
            'label' => 'required|string|max:255',
            'nama_instansi' => 'required|string|max:255',
            'id_provinsi' => 'nullable|exists:master_provinces,id',
            'id_kota' => 'nullable|exists:master_cities,id',
            'alamat' => 'nullable|string',
            'email' => 'nullable|email|max:255',
            'telepon' => 'nullable|string|max:20',
            'website' => 'nullable|url|max:255',
            'id_pemohon_1' => 'nullable|exists:pemohon,id',
            'latar_belakang' => 'required|string',
            'maksud_tujuan' => 'required|string',
            'lokasi_kerjasama' => 'nullable|string',
            'ruang_lingkup' => 'required|string',
            'jangka_waktu' => 'nullable|string|max:255',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_berakhir' => 'nullable|date|after_or_equal:tanggal_mulai',
            'manfaat' => 'nullable|string',
            'analisis_dampak' => 'nullable|string',
            'pembiayaan' => 'nullable|string',
            'opd_ids' => 'nullable|array',
            'opd_ids.*' => 'exists:opd,id',
        ]);

        $opdIds = $validated['opd_ids'] ?? null;
        unset($validated['opd_ids']);

        // Auto-compute jangka_waktu dari tanggal_mulai & tanggal_berakhir (safeguard server-side)
        $validated['jangka_waktu'] = $this->computeJangkaWaktu(
            $validated['tanggal_mulai'] ?? null,
            $validated['tanggal_berakhir'] ?? null
        ) ?: ($validated['jangka_waktu'] ?? null);

        // Jika permohonan sebelumnya ditolak, reset status ke Permohonan Baru (0)
        // dan hapus alasan tolak
        if ($wasRejected) {
            $validated['status'] = Permohonan::STATUS_PERMOHONAN;
            $validated['alasan_tolak'] = null;
        }

        $permohonan->update($validated);

        // Sync OPD jika dikirim
        if ($opdIds !== null) {
            $permohonan->opds()->sync($opdIds);
        }

        $deskripsi = $wasRejected
            ? 'Permohonan direvisi dan diajukan kembali setelah ditolak.'
            : 'Permohonan diperbarui.';

        PermohonanHistori::create([
            'id_permohonan' => $permohonan->id,
            'id_operator' => Auth::id(),
            'deskripsi' => $deskripsi,
        ]);

        $message = $wasRejected
            ? 'Revisi permohonan berhasil diajukan kembali.'
            : 'Permohonan berhasil diperbarui.';

        return redirect()->route("$this->prefix.show", $permohonan->uuid)
            ->with('success', $message);
    }

    /**
     * Remove the specified permohonan.
     */
    public function destroy(string $uuid)
    {
        $permohonan = $this->data::uuid($uuid)->firstOrFail();

        if (Auth::user()->hasRole('pemohon') && $permohonan->id_pemohon_0 != Auth::id()) {
            abort(403);
        }

        $permohonan->delete();

        return redirect()->route("$this->prefix.index")
            ->with('success', 'Permohonan berhasil dihapus.');
    }

    /**
     * Get kotas by provinsi (AJAX) - Usually public/authenticated helper
     */
    public function getKotas(int $provinsiId)
    {
        $kotas = Kota::where('province_id', $provinsiId)->orderBy('name')->get();
        return response()->json($kotas);
    }

    /**
     * Get all kotas grouped by provinsi (AJAX)
     */
    public function getAllKotas()
    {
        $provinsis = Provinsi::orderBy('name')->with([
            'kotas' => function ($q) {
                $q->orderBy('name');
            }
        ])->get();

        $result = $provinsis->map(function ($provinsi) {
            return [
                'provinsi' => $provinsi->name,
                'kotas' => $provinsi->kotas->map(function ($kota) {
                    return [
                        'id' => $kota->id,
                        'name' => $kota->name
                    ];
                })
            ];
        });

        return response()->json($result);
    }

    /**
     * Update status permohonan (Custom action)
     */
    public function updateStatus(Request $request, string $uuid)
    {
        $permohonan = $this->data::uuid($uuid)->firstOrFail();

        $validated = $request->validate([
            'status' => 'required|integer|in:0,1,2,4,7,8,9',
            'keterangan' => 'nullable|string|max:2000',
        ]);

        $oldStatus = $permohonan->status;

        // Aturan khusus tahap pelaksanaan:
        // - hanya boleh ditandai selesai (7)
        // - atau dicabut (8) dengan alasan wajib
        if ($oldStatus == Permohonan::STATUS_PELAKSANAAN) {
            if (!in_array($validated['status'], [Permohonan::STATUS_SELESAI, Permohonan::STATUS_DICABUT], true)) {
                return redirect()->back()->with('error', 'Status pelaksanaan hanya dapat diubah ke Selesai atau Dicabut.');
            }
            if ($validated['status'] == Permohonan::STATUS_DICABUT && empty(trim($validated['keterangan'] ?? ''))) {
                return redirect()->back()->with('error', 'Alasan pencabutan wajib diisi.');
            }
        }

        $updatePayload = ['status' => $validated['status']];
        if (in_array($validated['status'], [Permohonan::STATUS_DITOLAK, Permohonan::STATUS_DICABUT], true)) {
            $updatePayload['alasan_tolak'] = isset($validated['keterangan']) ? trim($validated['keterangan']) : null;
        } else {
            $updatePayload['alasan_tolak'] = null;
        }
        $permohonan->update($updatePayload);

        $statusLabels = Permohonan::statusLabels();
        $oldLabel = $statusLabels[$oldStatus]['label'] ?? 'Unknown';
        $newLabel = $statusLabels[$validated['status']]['label'] ?? 'Unknown';

        PermohonanHistori::create([
            'id_permohonan' => $permohonan->id,
            'id_operator' => Auth::id(),
            'deskripsi' => "Status diubah dari {$oldLabel} menjadi {$newLabel}.",
            'komentar' => $validated['keterangan'] ?? null,
        ]);

        // Notifikasi pemohon dan grup admin di-handle oleh PermohonanObserver via NotificationTemplate.

        return redirect()->back()->with('success', 'Status berhasil diperbarui.');
    }
    /**
     * Upload cooperation documents (after validation).
     */
    public function uploadFile(Request $request, string $uuid)
    {
        \Log::info('Upload files request received', [
            'uuid' => $uuid,
            'user_id' => Auth::id(),
            'files' => $request->allFiles()
        ]);

        $permohonan = $this->data::uuid($uuid)->firstOrFail();

        // Ownership check: Pemohon can only upload their own files
        // Admin/Operator can upload for any permohonan
        if (Auth::user()->hasRole('pemohon') && $permohonan->id_pemohon_0 != Auth::id()) {
            abort(403, 'Anda tidak memiliki akses untuk upload file ini.');
        }

        // Pemohon hanya melakukan upload berkas awal sekali. Jika ada berkas yang
        // perlu diperbaiki, upload ulang harus lewat alur revisi berkas ditolak.
        if (Auth::user()->hasRole('pemohon') && $permohonan->files()->exists()) {
            return redirect()->back()->with(
                'error',
                'Berkas sudah diupload. Upload ulang hanya tersedia pada berkas yang diminta revisi.'
            );
        }

        if (Auth::user()->hasRole('pemohon') && $permohonan->status != Permohonan::STATUS_PEMBAHASAN) {
            abort(403, 'Upload berkas hanya tersedia saat permohonan dalam tahap pembahasan.');
        }

        // Lock berkas pemohon setelah pembahasan selesai
        // Pemohon tidak bisa upload/ubah berkas tahap awal jika status sudah > PEMBAHASAN
        if (Auth::user()->hasRole('pemohon') && $permohonan->status > Permohonan::STATUS_PEMBAHASAN) {
            abort(403, 'Berkas tidak dapat diubah karena permohonan sudah melewati tahap pembahasan.');
        }

        $validated = $request->validate([
            // Req 3: Format dokumen ditetapkan
            'surat_permohonan' => 'required|file|mimes:pdf|max:10240',
            'proposal_kak'     => 'required|file|mimes:pdf|max:10240',
            'draft_mou'        => 'nullable|file|mimes:doc,docx|max:10240',
        ], [
            'surat_permohonan.required' => 'Surat Permohonan wajib diupload.',
            'proposal_kak.required' => 'Proposal/KAK wajib diupload.',
            'surat_permohonan.mimes' => 'Surat Permohonan harus berformat PDF.',
            'proposal_kak.mimes' => 'KAK harus berformat PDF.',
            'draft_mou.mimes' => 'Draft MoU harus berformat DOC atau DOCX.',
            'surat_permohonan.max' => 'Ukuran file maksimal 10MB.',
            'proposal_kak.max' => 'Ukuran file maksimal 10MB.',
            'draft_mou.max' => 'Ukuran file maksimal 10MB.',
        ]);

        \Log::info('Validation passed, processing files');

        $fileTypes = [
            'surat_permohonan' => 'Surat Permohonan Kerjasama',
            'proposal_kak' => 'Proposal / Kerangka Acuan Kerja',
            'draft_mou' => 'Draft MoU / Perjanjian Kerjasama',
        ];

        foreach ($fileTypes as $field => $label) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $filename = time() . '_' . $field . '_' . Str::slug($permohonan->kode) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('uploads/permohonan/' . $permohonan->id, $filename, 'public');

                \Log::info("File saved: $field", ['path' => $path]);

                PermohonanFile::create([
                    'id_permohonan' => $permohonan->id,
                    'label' => $label,
                    'file' => 'storage/' . $path,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'deskripsi' => 'Dokumen ' . $label . ' untuk permohonan ' . $permohonan->kode,
                    'status' => PermohonanFile::STATUS_DIPROSES,
                ]);
            }
        }

        // Log the upload activity
        PermohonanHistori::create([
            'id_permohonan' => $permohonan->id,
            'id_operator' => Auth::id(),
            'deskripsi' => 'Dokumen kerjasama telah diupload.',
        ]);

        // Send WhatsApp Notification to Admin Group — pakai template internal formal
        try {
            $wa = app(WhatsappService::class);
            $adminMsg = \App\Services\NotificationTemplate::berkasAwalDiupload($permohonan, Auth::user()?->name ?? 'Pemohon');
            $wa->sendToGroup($adminMsg);
        } catch (\Exception $e) {
            \Log::error("Failed to send WA Notification: " . $e->getMessage());
        }

        return redirect()->route("$this->prefix.index")
            ->with('success', 'Dokumen berhasil diupload. Permohonan akan segera diproses.');
    }
    /**
     * Get discussion history for a specific file.
     */
    public function getFileDiskusi(string $uuid)
    {
        $file = PermohonanFile::where('uuid', $uuid)->firstOrFail();

        // Ensure user has access
        $permohonan = $file->permohonan;
        if (Auth::user()->hasRole('pemohon') && $permohonan->id_pemohon_0 != Auth::id()) {
            abort(403);
        }

        $histori = PermohonanHistori::where('id_file', $file->id)
            ->with(['operator'])
            ->orderBy('created_at')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'operator' => $item->operator,
                    'komentar' => $item->komentar ?? $item->deskripsi,
                    'created_at' => $item->created_at,
                    'formatted_time' => $item->created_at->format('d M Y H:i'),
                    'is_me' => $item->id_operator === Auth::id(),
                ];
            });

        return response()->json($histori);
    }

    /**
     * Store a discussion message for a file.
     */
    public function storeFileDiskusi(Request $request, string $uuid)
    {
        $file = PermohonanFile::where('uuid', $uuid)->firstOrFail();

        $request->validate(['message' => 'required|string']);

        $histori = PermohonanHistori::create([
            'id_permohonan' => $file->id_permohonan,
            'id_operator' => Auth::id(),
            'id_file' => $file->id,
            'deskripsi' => 'Komentar baru.',
            'komentar' => $request->message,
        ]);

        return response()->json([
            'id' => $histori->id,
            'operator' => Auth::user(),
            'komentar' => $histori->komentar,
            'formatted_time' => $histori->created_at->format('d M Y H:i'),
            'is_me' => true,
        ]);
    }

    /**
     * Review a file (Approve/Reject) - Admin only.
     */
    public function reviewFile(Request $request, string $uuid)
    {
        $file = PermohonanFile::where('uuid', $uuid)->firstOrFail();

        // Ensure user is admin/verifier
        if (!Auth::user()->hasRole(['administrator', 'superadmin', 'verifikator', 'tkksd'])) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'status' => 'required|integer|in:1,2', // 1=Approved, 2=Rejected
            'komentar' => 'nullable|string',
        ]);

        // Check if this user already approved this file (prevent duplicate)
        if ($validated['status'] == 1) {
            $alreadyApproved = PermohonanHistori::where('id_file', $file->id)
                ->where('id_operator', Auth::id())
                ->where('deskripsi', 'LIKE', '%Disetujui%')
                ->exists();

            if ($alreadyApproved) {
                $approvals = $this->getFileApprovals($file);
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah menyetujui dokumen ini sebelumnya.',
                    'already_approved' => true,
                    'file' => $file,
                    'approvals_count' => $approvals['count'],
                    'approved_by' => $approvals['users'],
                ], 409);
            }
        }

        $file->update([
            'status' => $validated['status'],
            'komentar' => $validated['komentar'] ?? null,
            'reviewer_id' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        $statusLabel = $validated['status'] == 1 ? 'Disetujui' : 'Ditolak';
        $userRole = Auth::user()->role_name;

        PermohonanHistori::create([
            'id_permohonan' => $file->id_permohonan,
            'id_operator' => Auth::id(),
            'role_operator' => $userRole,
            'id_file' => $file->id,
            'deskripsi' => "Dokumen {$file->label} telah {$statusLabel} oleh " . Auth::user()->name . " ({$userRole}).",
            'komentar' => $validated['komentar'] ?? null,
        ]);

        $approvals = $this->getFileApprovals($file);

        return response()->json([
            'message' => 'Success',
            'file' => $file,
            'approvals_count' => $approvals['count'],
            'approved_by' => $approvals['users'],
        ]);
    }

    /**
     * Get approval info for a file
     */
    private function getFileApprovals(PermohonanFile $file): array
    {
        $approvals = PermohonanHistori::where('id_file', $file->id)
            ->where('deskripsi', 'LIKE', '%Disetujui%')
            ->with('operator:id,name,nickname')
            ->get()
            ->unique('id_operator');

        return [
            'count' => $approvals->count(),
            'users' => $approvals->map(fn($a) => [
                'id' => $a->id_operator,
                'name' => $a->operator->name ?? 'Unknown',
                'role' => $a->role_operator ?? '-',
                'timestamp' => $a->created_at?->format('d M Y H:i'),
            ])->values()->toArray(),
        ];
    }

    /**
     * Upload a revision for a rejected file.
     */
    public function uploadFileRevision(Request $request, string $uuid)
    {
        $file = PermohonanFile::where('uuid', $uuid)->firstOrFail();

        // Lock berkas setelah pembahasan selesai
        if (Auth::user()->hasRole('pemohon') && $file->permohonan->status > Permohonan::STATUS_PEMBAHASAN) {
            abort(403, 'Revisi tidak diizinkan setelah permohonan melewati tahap pembahasan.');
        }

        // Ensure strictly for Rejected files
        if ($file->status != PermohonanFile::STATUS_DITOLAK) {
            abort(400, 'File is not in rejected status.');
        }

        $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240',
        ]);

        // Upload new file
        $newFile = $request->file('file');
        $filename = time() . '_REVISION_' . $file->label . '.' . $newFile->getClientOriginalExtension();
        $path = $newFile->storeAs('uploads/permohonan/' . $file->id_permohonan, $filename, 'public');

        $file->update([
            'file' => 'storage/' . $path,
            'file_path' => $path,
            'file_name' => $newFile->getClientOriginalName(),
            'status' => PermohonanFile::STATUS_DIPROSES, // Reset to Pending
            'komentar' => null, // Clear rejection comment
        ]);

        PermohonanHistori::create([
            'id_permohonan' => $file->id_permohonan,
            'id_operator' => Auth::id(),
            'id_file' => $file->id,
            'deskripsi' => "Revisi dokumen {$file->label} diupload.",
        ]);

        return response()->json(['message' => 'Revision uploaded', 'file' => $file]);
    }

    /**
     * Hitung jangka waktu kerjasama otomatis dari tanggal_mulai → tanggal_berakhir.
     * Output: "X Tahun, Y Bulan, Z Hari" (komponen 0 di-skip).
     */
    protected function computeJangkaWaktu(?string $mulai, ?string $akhir): ?string
    {
        if (!$mulai || !$akhir) return null;
        try {
            $start = \Carbon\Carbon::parse($mulai);
            $end = \Carbon\Carbon::parse($akhir);
        } catch (\Throwable $e) {
            return null;
        }
        if ($end->lt($start)) return null;

        $diff = $start->diff($end);
        $parts = [];
        if ($diff->y > 0) $parts[] = "{$diff->y} Tahun";
        if ($diff->m > 0) $parts[] = "{$diff->m} Bulan";
        if ($diff->d > 0) $parts[] = "{$diff->d} Hari";
        return $parts ? implode(', ', $parts) : '0 Hari';
    }
}
