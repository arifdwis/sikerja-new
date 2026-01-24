<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class PembahasanController extends Controller implements HasMiddleware
{
    protected $title;
    protected $prefix;
    protected $view;
    protected $share;

    public function __construct()
    {
        $this->title = 'Pembahasan Permohonan';
        $this->view = 'Backend/Pembahasan';
        $this->prefix = 'pembahasan';

        $this->share = [
            'title' => $this->title,
            'view' => $this->view,
            'prefix' => $this->prefix
        ];
    }

    public static function middleware(): array
    {
        return [
            new Middleware('can:permohonan.menu.pembahasan', only: ['index', 'update']),
        ];
    }

    public function index(Request $request)
    {
        $query = Permohonan::with(['kategori', 'pemohon1', 'operator.corporate', 'provinsi', 'kota', 'files'])
            ->where('status', Permohonan::STATUS_PEMBAHASAN) // Status 1: Dalam Pembahasan
            ->latest();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('label', 'like', "%{$search}%")
                    ->orWhere('nomor_permohonan', 'like', "%{$search}%")
                    ->orWhere('nama_instansi', 'like', "%{$search}%");
            });
        }

        $datas = $query->paginate(10)->withQueryString();

        return Inertia::render("$this->view/Index", [
            'datas' => $datas,
            'share' => $this->share,
            'filters' => $request->only(['search']),
        ]);
    }

    public function show(string $uuid)
    {
        $permohonan = Permohonan::with(['kategori', 'pemohon1', 'pemohon2', 'operator.corporate', 'provinsi', 'kota', 'files'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        // Calculate if all files are approved
        $allFilesApproved = $permohonan->files->count() > 0 &&
            $permohonan->files->every(fn($file) => $file->status === 1);

        $permohonan->all_files_approved = $allFilesApproved;

        return response()->json($permohonan);
    }

    public function update(Request $request, string $uuid)
    {
        $permohonan = Permohonan::with('files')->where('uuid', $uuid)->firstOrFail();

        // Check if all files are approved
        $allFilesApproved = $permohonan->files->count() > 0 &&
            $permohonan->files->every(fn($file) => $file->status === 1);

        if (!$allFilesApproved) {
            return redirect()->back()->with('error', 'Semua dokumen harus disetujui sebelum melanjutkan ke penjadwalan.');
        }

        // Pembahasan selesai -> masuk ke Penjadwalan (pemohon buat jadwal meeting)
        $permohonan->update([
            'status' => Permohonan::STATUS_PENJADWALAN, // Status 2: Menunggu Penjadwalan
        ]);

        return redirect()->back()->with('success', 'Pembahasan selesai. Pemohon dapat membuat jadwal pertemuan.');
    }
}
