<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ValidasiController extends Controller implements HasMiddleware
{
    protected $title;
    protected $prefix;
    protected $view;
    protected $share;

    public function __construct()
    {
        $this->title = 'Validasi Permohonan';
        $this->view = 'Backend/Validasi';
        $this->prefix = 'validasi';

        $this->share = [
            'title' => $this->title,
            'view' => $this->view,
            'prefix' => $this->prefix
        ];
    }

    public static function middleware(): array
    {
        return [
            new Middleware('can:permohonan.menu.validasi', only: ['index', 'update']),
        ];
    }

    public function index(Request $request)
    {
        $query = Permohonan::with(['kategori', 'pemohon1', 'pemohon2', 'operator.corporate', 'provinsi', 'kota'])
            ->where('status', Permohonan::STATUS_PERMOHONAN) // 0
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

        return response()->json($permohonan);
    }

    public function update(Request $request, string $uuid)
    {
        $permohonan = Permohonan::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'status' => 'required|in:1,9,99', // 1: Terima (Pembahasan), 9: Tolak Final, 99: Tolak/Revisi (alias 9)
            'keterangan' => 'required_unless:status,1|string|min:5', // Alasan wajib jika ditolak
        ], [
            'keterangan.required_unless' => 'Alasan penolakan wajib diisi (minimal 5 karakter).',
            'keterangan.min' => 'Alasan penolakan minimal 5 karakter.',
        ]);

        if ($validated['status'] == 1) {
            $permohonan->update([
                'status' => Permohonan::STATUS_PEMBAHASAN, // Status 1: Masuk Pembahasan
                'alasan_tolak' => null, // Clear alasan tolak jika sebelumnya pernah ditolak
            ]);
            $message = 'Permohonan berhasil divalidasi dan masuk ke tahap Pembahasan.';
            $notifStatus = 'Dalam Pembahasan (Validasi Diterima)';
        } else {
            // Tolak: status 9 = DITOLAK, simpan alasan tolak
            $permohonan->update([
                'status' => Permohonan::STATUS_DITOLAK, // 9
                'alasan_tolak' => $validated['keterangan'],
            ]);

            // Histori dengan role admin
            \App\Models\PermohonanHistori::create([
                'id_permohonan' => $permohonan->id,
                'id_operator' => Auth::id(),
                'role_operator' => Auth::user()->role_name,
                'deskripsi' => 'Permohonan ditolak / dikembalikan untuk revisi.',
                'komentar' => $validated['keterangan'],
            ]);

            $message = 'Permohonan ditolak. Pemohon dapat melakukan revisi.';
            $notifStatus = 'Ditolak / Perlu Revisi';
        }

        // Notifikasi pemohon & grup admin di-handle oleh PermohonanObserver
        // saat status berubah (pakai App\Services\NotificationTemplate untuk format formal).

        return redirect()->back()->with('success', $message);
    }
}
