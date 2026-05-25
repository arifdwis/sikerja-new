<?php

namespace App\Http\Controllers;

use App\Models\KerjasamaManual;
use App\Models\Kategori;
use App\Models\Opd;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class KerjasamaManualController extends Controller implements HasMiddleware
{
    protected $title;
    protected $prefix;
    protected $view;
    protected $share;

    public function __construct()
    {
        $this->title = 'Kerjasama Manual';
        $this->view = 'Backend/KerjasamaManual';
        $this->prefix = 'kerjasama-manual';

        $this->share = [
            'title' => $this->title,
            'view' => $this->view,
            'prefix' => $this->prefix,
        ];
    }

    public static function middleware(): array
    {
        // Hanya admin/superadmin
        return [
            new Middleware('role:administrator,superadmin'),
        ];
    }

    public function index(Request $request)
    {
        $query = KerjasamaManual::with(['kategori', 'opds', 'creator'])->latest();

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_instansi', 'like', "%{$search}%")
                    ->orWhere('label', 'like', "%{$search}%")
                    ->orWhere('nomor_pks', 'like', "%{$search}%");
            });
        }

        $datas = $query->paginate(15)->withQueryString();

        return Inertia::render("$this->view/Index", [
            'datas' => $datas,
            'kategoris' => Kategori::orderBy('label')->get(),
            'opds' => Opd::active()->orderBy('nama')->get(),
            'share' => $this->share,
            'filters' => $request->only(['search', 'detail', 'edit', 'create']),
        ]);
    }

    public function create()
    {
        return redirect()->route("$this->prefix.index", ['create' => 1]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nomor_pks' => 'nullable|string|max:255',
            'nama_instansi' => 'required|string|max:255',
            'label' => 'required|string|max:255',
            'id_kategori' => 'nullable|exists:kategori,id',
            'ruang_lingkup' => 'nullable|string',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_berakhir' => 'nullable|date|after_or_equal:tanggal_mulai',
            'jangka_waktu' => 'nullable|string|max:255',
            'file' => 'required|file|mimes:pdf|max:20480',
            'opd_ids' => 'nullable|array',
            'opd_ids.*' => 'exists:opd,id',
        ], [
            'file.required' => 'Upload PKS final wajib.',
            'file.mimes' => 'File harus berformat PDF.',
        ]);

        $opdIds = $validated['opd_ids'] ?? [];
        unset($validated['opd_ids']);
        $validated['jangka_waktu'] = $this->calculateJangkaWaktu(
            $validated['tanggal_mulai'] ?? null,
            $validated['tanggal_berakhir'] ?? null,
        );

        $file = $request->file('file');
        $filename = time() . '_KS_MANUAL_' . Str::random(6) . '.pdf';
        $path = $file->storeAs('uploads/kerjasama_manual', $filename, 'public');

        $validated['file_pks'] = $path;
        $validated['file_pks_name'] = $file->getClientOriginalName();
        $validated['created_by'] = Auth::id();

        $km = KerjasamaManual::create($validated);

        if (!empty($opdIds)) {
            $km->opds()->sync($opdIds);
        }

        return redirect()->route("$this->prefix.show", $km->uuid)
            ->with('success', 'Kerjasama manual berhasil ditambahkan.');
    }

    public function show(Request $request, string $uuid)
    {
        $km = KerjasamaManual::with(['kategori', 'opds', 'creator'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        if ($request->expectsJson()) {
            return response()->json($km);
        }

        return redirect()->route("$this->prefix.index", ['detail' => $km->uuid]);
    }

    public function edit(Request $request, string $uuid)
    {
        $km = KerjasamaManual::with('opds')->where('uuid', $uuid)->firstOrFail();

        if ($request->expectsJson()) {
            return response()->json([
                'data' => $km,
                'selectedOpdIds' => $km->opds->pluck('id')->values(),
            ]);
        }

        return redirect()->route("$this->prefix.index", ['edit' => $km->uuid]);
    }

    public function update(Request $request, string $uuid)
    {
        $km = KerjasamaManual::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'nomor_pks' => 'nullable|string|max:255',
            'nama_instansi' => 'required|string|max:255',
            'label' => 'required|string|max:255',
            'id_kategori' => 'nullable|exists:kategori,id',
            'ruang_lingkup' => 'nullable|string',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_berakhir' => 'nullable|date|after_or_equal:tanggal_mulai',
            'jangka_waktu' => 'nullable|string|max:255',
            'file' => 'nullable|file|mimes:pdf|max:20480',
            'opd_ids' => 'nullable|array',
            'opd_ids.*' => 'exists:opd,id',
        ]);

        $opdIds = $validated['opd_ids'] ?? null;
        unset($validated['opd_ids']);
        $validated['jangka_waktu'] = $this->calculateJangkaWaktu(
            $validated['tanggal_mulai'] ?? null,
            $validated['tanggal_berakhir'] ?? null,
        );

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_KS_MANUAL_' . Str::random(6) . '.pdf';
            $path = $file->storeAs('uploads/kerjasama_manual', $filename, 'public');
            $validated['file_pks'] = $path;
            $validated['file_pks_name'] = $file->getClientOriginalName();
        }

        $km->update($validated);

        if ($opdIds !== null) {
            $km->opds()->sync($opdIds);
        }

        return redirect()->route("$this->prefix.show", $km->uuid)
            ->with('success', 'Kerjasama manual berhasil diperbarui.');
    }

    public function destroy(string $uuid)
    {
        $km = KerjasamaManual::where('uuid', $uuid)->firstOrFail();
        $km->delete();

        return redirect()->route("$this->prefix.index")
            ->with('success', 'Kerjasama manual berhasil dihapus.');
    }

    private function calculateJangkaWaktu(?string $tanggalMulai, ?string $tanggalBerakhir): ?string
    {
        if (!$tanggalMulai || !$tanggalBerakhir) {
            return null;
        }

        $start = Carbon::parse($tanggalMulai)->startOfDay();
        $inclusiveEnd = Carbon::parse($tanggalBerakhir)->startOfDay()->addDay();
        $interval = $start->diff($inclusiveEnd);
        $parts = [];

        if ($interval->y) {
            $parts[] = "{$interval->y} Tahun";
        }

        if ($interval->m) {
            $parts[] = "{$interval->m} Bulan";
        }

        if ($interval->d) {
            $parts[] = "{$interval->d} Hari";
        }

        return implode(' ', $parts) ?: '1 Hari';
    }
}
