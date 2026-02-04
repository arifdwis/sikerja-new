<?php

namespace App\Http\Controllers;

use App\Models\Monev;
use App\Models\Permohonan;
use App\Models\Pemohon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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
            new Middleware('can:monev.review', only: ['review']),
        ];
    }

    /**
     * Display list of Monev
     * Admin: see all, Pemohon: see own
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user->can('monev.menu.admin');

        $query = Monev::with(['permohonan.kategori', 'pemohon', 'reviewer'])
            ->latest();

        // Pemohon only sees their own
        if (!$isAdmin) {
            $pemohon = Pemohon::where('id_operator', $user->id)->first();
            if ($pemohon) {
                $query->where('id_pemohon', $pemohon->id);
            } else {
                // Also check by user id directly via permohonan
                $query->whereHas('permohonan', function ($q) use ($user) {
                    $q->where('id_pemohon_0', $user->id);
                });
            }
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Search
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

        // Get kerjasama that have expired (tanggal_berakhir passed) and don't have monev yet
        $pendingPermohonans = [];
        if ($isAdmin) {
            // Admin sees all finished kerjasama without monev
            // A kerjasama is considered finished when:
            // 1. Status is SELESAI (signed agreement)
            // 2. tanggal_berakhir has passed (the agreement period ended)
            $pendingPermohonans = Permohonan::with(['kategori'])
                ->where('status', Permohonan::STATUS_SELESAI)
                ->whereNotNull('tanggal_berakhir')
                ->where('tanggal_berakhir', '<', now())
                ->whereDoesntHave('monev')
                ->latest()
                ->get();
        }

        return Inertia::render("$this->view/Index", [
            'datas' => $datas,
            'pendingPermohonans' => $pendingPermohonans,
            'share' => $this->share,
            'filters' => $request->only(['search', 'status']),
            'isAdmin' => $isAdmin,
        ]);
    }

    /**
     * Show form to create new Monev
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        $pemohon = Pemohon::where('id_operator', $user->id)->first();

        // Get completed permohonan that don't have monev yet
        $permohonans = Permohonan::where('id_pemohon_0', $user->id)
            ->where('status', Permohonan::STATUS_SELESAI)
            ->whereDoesntHave('monev')
            ->get(['id', 'uuid', 'label', 'nama_instansi', 'nomor_permohonan']);

        // If specific permohonan is requested
        $selectedPermohonan = null;
        if ($request->has('permohonan')) {
            $selectedPermohonan = Permohonan::where('uuid', $request->permohonan)
                ->where('id_pemohon_0', $user->id)
                ->first();
        }

        return Inertia::render("$this->view/Create", [
            'share' => array_merge($this->share, ['title' => 'Isi Form Monev']),
            'permohonans' => $permohonans,
            'selectedPermohonan' => $selectedPermohonan,
            'pemohon' => $pemohon,
        ]);
    }

    /**
     * Store new Monev
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $pemohon = Pemohon::where('id_operator', $user->id)->first();

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
            'file_bukti' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        // Handle file upload
        if ($request->hasFile('file_bukti')) {
            $file = $request->file('file_bukti');
            $filename = 'monev_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('monev', $filename, 'public');
            $validated['file_bukti'] = $path;
        }

        $validated['id_pemohon'] = $pemohon?->id;
        $validated['status'] = Monev::STATUS_REVIEWED; // Admin creates directly, no review needed

        $monev = Monev::create($validated);

        return redirect()->route('monev.show', $monev->uuid)
            ->with('success', 'Form Monev berhasil disimpan.');
    }

    /**
     * Show Monev detail
     */
    public function show(string $uuid)
    {
        $user = Auth::user();
        $isAdmin = $user->can('monev.menu.admin');

        $monev = Monev::with(['permohonan.kategori', 'pemohon', 'reviewer'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        // Check access
        if (!$isAdmin) {
            $pemohon = Pemohon::where('id_operator', $user->id)->first();
            $hasAccess = ($pemohon && $monev->id_pemohon === $pemohon->id) ||
                ($monev->permohonan && $monev->permohonan->id_pemohon_0 === $user->id);

            if (!$hasAccess) {
                abort(403, 'Akses ditolak.');
            }
        }

        return Inertia::render("$this->view/Show", [
            'share' => array_merge($this->share, ['title' => 'Detail Monev']),
            'monev' => $monev,
            'isAdmin' => $isAdmin,
        ]);
    }

    /**
     * Admin review Monev
     */
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

        return redirect()->back()->with('success', 'Monev berhasil direview.');
    }
}
