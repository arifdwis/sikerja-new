<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use App\Models\Kategori;
use App\Models\Provinsi;
use App\Models\Kota;
use App\Models\Pemohon;
use App\Models\Corporate;
use App\Models\PermohonanHistori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
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
            'penjadwalans' => function ($q) {
                $q->latest();
            }
        ]);
        $routeName = Route::currentRouteName();
        $pageTitle = $this->title;

        // Filter based on Route Name (untuk menu Sidebar)
        if ($routeName === 'validasi.index') {
            $query->whereIn('status', [Permohonan::STATUS_PERMOHONAN, Permohonan::STATUS_DISPOSISI]);
            $pageTitle = 'Validasi Permohonan';
        } elseif ($routeName === 'pembahasan.index') {
            $query->where('status', Permohonan::STATUS_PEMBAHASAN);
            $pageTitle = 'Pembahasan Permohonan';
        } elseif ($routeName === 'persetujuan.index') {
            $query->where('status', Permohonan::STATUS_SELESAI);
            $pageTitle = 'Persetujuan Kerjasama';
        } elseif ($routeName === 'permohonan.selesai') {
            $query->where('status', Permohonan::STATUS_SELESAI);
            $pageTitle = 'Permohonan Selesai';
        }

        // Filter by user role (Ownership Scope)
        // Pemohon hanya melihat data miliknya sendiri
        if (Auth::user()->hasRole('pemohon')) {
            $query->where('id_pemohon_0', Auth::id());
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

        return Inertia::render("$this->view/Index", [
            'permohonan' => $permohonan,
            'statusLabels' => $statusLabels,
            'kategoris' => $kategoris,
            'provinsis' => $provinsis,
            'pemohon' => $pemohon,
            'corporate' => $corporate,
            'pemohonanList' => $pemohonanList,
            'filters' => $request->only(['status', 'tahun', 'search']),
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
        $query = $this->data::with(['kategori', 'operator', 'kota']);
        $pageTitle = 'Riwayat Permohonan';

        // Ownership Check (Strictly for Pemohon)
        if (Auth::user()->hasRole('pemohon')) {
            $query->where('id_pemohon_0', Auth::id());
        }

        $query->where('status', Permohonan::STATUS_SELESAI);

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('label', 'like', "%{$search}%")
                    ->orWhere('nama_instansi', 'like', "%{$search}%")
                    ->orWhere('nomor_permohonan', 'like', "%{$search}%");
            });
        }

        $datas = $query->latest()->paginate(15)->withQueryString();

        return Inertia::render("Riwayat/Index", [
            'datas' => $datas,
            'filters' => $request->only(['search']),
            'share' => [
                'title' => $pageTitle,
                'prefix' => 'riwayat', // Ensure route('riwayat.index') works if we name the route that
            ],
        ]);
    }

    /**
     * Show the form for creating a new permohonan.
     */
    public function create()
    {
        $user = Auth::user();
        $kategoris = Kategori::all();
        $provinsis = Provinsi::all();

        // Load pemohon and corporate data for auto-fill
        $pemohon = null;
        $corporate = null;
        $pemohonanList = [];

        if ($user->hasRole('pemohon')) {
            $pemohon = Pemohon::where('id_operator', $user->id)->first();
            $corporate = Corporate::where('id_operator', $user->id)->first();
        }

        // Load all pemohon for PPKSD-2 dropdown
        $pemohonanList = Pemohon::orderBy('name')->get(['id', 'name', 'jabatan', 'unit_kerja']);

        return Inertia::render("$this->view/Create", [
            'kategoris' => $kategoris,
            'provinsis' => $provinsis,
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
            'manfaat' => 'nullable|string',
            'analisis_dampak' => 'nullable|string',
            'pembiayaan' => 'nullable|string',
        ]);

        $validated['kode'] = 'PKS-' . strtoupper(Str::random(8));
        $validated['nomor_permohonan'] = 'REQ/' . date('Y') . '/' . str_pad(Permohonan::whereYear('created_at', date('Y'))->count() + 1, 4, '0', STR_PAD_LEFT);
        $validated['id_pemohon_0'] = Auth::id();
        $validated['status'] = Permohonan::STATUS_PERMOHONAN;

        $permohonan = $this->data::create($validated);

        PermohonanHistori::create([
            'id_permohonan' => $permohonan->id,
            'id_operator' => Auth::id(),
            'deskripsi' => 'Permohonan kerjasama dibuat.',
        ]);

        return redirect()->route("$this->prefix.show", $permohonan->uuid)
            ->with('success', 'Permohonan berhasil dibuat.');
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
            'historis.operator',
            'penjadwalans' => function ($q) {
                $q->latest()->with('approver');
            }
        ])->uuid($uuid)->firstOrFail();

        // Ownership check can be handled by Policy, or kept here if Policy not set up
        if (Auth::user()->hasRole('pemohon') && $permohonan->id_pemohon_0 != Auth::id()) {
            abort(403, 'Unauthorized access to this permohonan');
        }

        $statusLabels = Permohonan::statusLabels();

        if (request()->wantsJson()) {
            return response()->json($permohonan);
        }

        return Inertia::render("$this->view/Show", [
            'permohonan' => $permohonan,
            'statusLabels' => $statusLabels,
            'share' => $this->share,
        ]);
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

        $kategoris = Kategori::all();
        $provinsis = Provinsi::all();
        $kotas = Kota::where('province_id', $permohonan->id_provinsi)->get();

        return Inertia::render("$this->view/Edit", [
            'permohonan' => $permohonan,
            'kategoris' => $kategoris,
            'provinsis' => $provinsis,
            'kotas' => $kotas,
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

        $validated = $request->validate([
            'id_kategori' => 'required|exists:kategori,id',
            'label' => 'required|string|max:255',
            'nama_instansi' => 'required|string|max:255',
            'id_provinsi' => 'required|exists:provinsi,id',
            'id_kota' => 'required|exists:kota,id',
            'alamat' => 'nullable|string',
            'email' => 'nullable|email|max:255',
            'telepon' => 'nullable|string|max:20',
            'website' => 'nullable|url|max:255',
            'latar_belakang' => 'required|string',
            'maksud_tujuan' => 'required|string',
            'ruang_lingkup' => 'required|string',
            'jangka_waktu' => 'nullable|string|max:100',
            'manfaat' => 'nullable|string',
            'analisis_dampak' => 'nullable|string',
            'pembiayaan' => 'nullable|string',
        ]);

        $permohonan->update($validated);

        PermohonanHistori::create([
            'id_permohonan' => $permohonan->id,
            'id_operator' => Auth::id(),
            'deskripsi' => 'Permohonan diperbarui.',
        ]);

        return redirect()->route("$this->prefix.show", $permohonan->uuid)
            ->with('success', 'Permohonan berhasil diperbarui.');
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
            'status' => 'required|integer|in:0,1,2,4,9',
            'keterangan' => 'nullable|string',
        ]);

        $oldStatus = $permohonan->status;
        $permohonan->update(['status' => $validated['status']]);

        if ($validated['status'] == Permohonan::STATUS_DITOLAK && isset($validated['keterangan'])) {
            $permohonan->update(['alasan_tolak' => $validated['keterangan']]);
        }

        $statusLabels = Permohonan::statusLabels();
        $oldLabel = $statusLabels[$oldStatus]['label'] ?? 'Unknown';
        $newLabel = $statusLabels[$validated['status']]['label'] ?? 'Unknown';

        PermohonanHistori::create([
            'id_permohonan' => $permohonan->id,
            'id_operator' => Auth::id(),
            'deskripsi' => "Status diubah dari {$oldLabel} menjadi {$newLabel}.",
            'komentar' => $validated['keterangan'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Status berhasil diperbarui.');
    }
    /**
     * Upload cooperation documents (after validation).
     */
    public function uploadFiles(Request $request, string $uuid)
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

        $validated = $request->validate([
            'surat_permohonan' => 'required|file|mimes:pdf,doc,docx|max:10240',
            'proposal_kak' => 'required|file|mimes:pdf,doc,docx|max:10240',
            'draft_mou' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ], [
            'surat_permohonan.required' => 'Surat Permohonan wajib diupload.',
            'proposal_kak.required' => 'Proposal/KAK wajib diupload.',
            'surat_permohonan.mimes' => 'Format file harus PDF, DOC, atau DOCX.',
            'proposal_kak.mimes' => 'Format file harus PDF, DOC, atau DOCX.',
            'draft_mou.mimes' => 'Format file harus PDF, DOC, atau DOCX.',
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

                \App\Models\PermohonanFile::create([
                    'id_permohonan' => $permohonan->id,
                    'label' => $label,
                    'file' => 'storage/' . $path,
                    'deskripsi' => 'Dokumen ' . $label . ' untuk permohonan ' . $permohonan->kode,
                    'status' => \App\Models\PermohonanFile::STATUS_DIPROSES,
                ]);
            }
        }

        // Log the upload activity
        PermohonanHistori::create([
            'id_permohonan' => $permohonan->id,
            'id_operator' => Auth::id(),
            'deskripsi' => 'Dokumen kerjasama telah diupload.',
        ]);

        \Log::info('Upload completed successfully');

        return redirect()->route("$this->prefix.index")
            ->with('success', 'Dokumen berhasil diupload. Permohonan akan segera diproses.');
    }
}
