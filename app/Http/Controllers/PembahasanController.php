<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use Illuminate\Support\Facades\Auth;
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
        $userId = Auth::id();
        $query = Permohonan::with(['kategori', 'pemohon1', 'operator.corporate', 'provinsi', 'kota', 'files'])
            ->withExists([
                // "contributed" = user ini sudah memberikan masukan/diskusi pada salah satu dokumen permohonan
                // Indikator: ada PermohonanHistori dengan id_file (artinya komentar/aksi pada dokumen) oleh user ini.
                'historis as contributed' => function ($q) use ($userId) {
                    $q->where('id_operator', $userId)->whereNotNull('id_file');
                }
            ])
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
            'isRiwayat' => false
        ]);
    }

    public function riwayat(Request $request)
    {
        $userId = Auth::id();
        // Riwayat: Items I have contributed to, regardless of status (or maybe finished ones?)
        // Let's assume History = I contributed AND they might not be in active discussion anymore, or simplified list of all my contributions.
        // Usually "Riwayat" matches "History of completed tasks".
        // But user said "mana yang sudah dia lakukan pembahasan". This implies "My Done List".

        $query = Permohonan::with(['kategori', 'pemohon1', 'operator.corporate', 'provinsi', 'kota'])
            ->whereHas('historis', function ($q) use ($userId) {
                $q->where('id_operator', $userId)->whereNotNull('id_file');
            })
            // Optional: Exclude current active discussions if they are in the main list?
            // ->where('status', '!=', Permohonan::STATUS_PEMBAHASAN) 
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

        // Mark all as contributed since we filtered by it
        $datas->getCollection()->transform(function ($item) {
            $item->contributed = true;
            return $item;
        });

        return Inertia::render("$this->view/Index", [
            'datas' => $datas,
            'share' => array_merge($this->share, ['title' => 'Riwayat Pembahasan Saya']),
            'filters' => $request->only(['search']),
            'isRiwayat' => true
        ]);
    }

    public function arsip(Request $request)
    {
        $userId = Auth::id();
        $query = Permohonan::with(['kategori', 'pemohon1', 'operator.corporate', 'provinsi', 'kota', 'files'])
            ->withExists([
                'historis as contributed' => function ($q) use ($userId) {
                    $q->where('id_operator', $userId)->whereNotNull('id_file');
                }
            ])
            ->where('status', '>', Permohonan::STATUS_PEMBAHASAN) // Status > 1 (Selesai/Penjadwalan/Ditolak)
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
            'share' => array_merge($this->share, ['title' => 'Arsip Pembahasan']),
            'filters' => $request->only(['search']),
            'isRiwayat' => true // Reuse read-only view logic
        ]);
    }

    public function show(string $uuid)
    {
        $permohonan = Permohonan::with(['kategori', 'pemohon1', 'pemohon2', 'operator.corporate', 'provinsi', 'kota', 'files'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        // Calculate if all files are approved
        $allFilesApproved = $permohonan->files->count() > 0 &&
            $permohonan->files->every(fn($file) => $file->status == 1);

        $permohonan->all_files_approved = $allFilesApproved;

        return response()->json($permohonan);
    }

    public function update(Request $request, string $uuid)
    {
        $permohonan = Permohonan::with('files')->where('uuid', $uuid)->firstOrFail();

        // Validate current status is still in pembahasan
        if ($permohonan->status != Permohonan::STATUS_PEMBAHASAN) {
            abort(422, 'Status permohonan sudah berubah dan tidak bisa diproses.');
        }

        // Check if all files are approved (Optional validation)
        $allFilesApproved = $permohonan->files->count() > 0 &&
            $permohonan->files->every(fn($file) => $file->status == 1);

        // Allow Admin to proceed regardless, or just warn? 
        // User requested that administrator CAN approve.
        // We will remove the server-side block.

        // Pembahasan selesai -> masuk ke Penjadwalan (pemohon buat jadwal meeting)
        $permohonan->update([
            'status' => Permohonan::STATUS_PENJADWALAN, // Status 2: Menunggu Penjadwalan
        ]);

        // Notifikasi pemohon & grup admin di-handle PermohonanObserver via NotificationTemplate.

        return redirect()->back()->with('success', 'Pembahasan selesai. Pemohon dapat mengajukan jadwal penandatanganan.');
    }
}
