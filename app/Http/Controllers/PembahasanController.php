<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use App\Models\User;
use App\Services\WhatsappService;
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

        // Send WhatsApp Notification
        try {
            $wa = app(WhatsappService::class);

            // 1. Notify User (Pemohon) - FORMAL
            $formalMsg = "*SIKERJA - PEMBARUAN STATUS*\n\n" .
                "Yth. Pemohon Kerja Sama,\n" .
                "Berikut kami sampaikan status terbaru permohonan Anda:\n\n" .
                "Instansi: *{$permohonan->nama_instansi}*\n" .
                "Perihal: {$permohonan->label}\n" .
                "Status: *Menunggu Penjadwalan*\n" .
                "Catatan: Pembahasan selesai. Silakan buat jadwal pertemuan.\n\n" .
                "Terima kasih.\n" .
                "_Pemerintah Kota Samarinda_";

            // Try getting phone from User first, then Pemohon profile
            $targetPhone = null;
            $user = User::find($permohonan->id_pemohon_0);
            if ($user && !empty($user->phone)) {
                $targetPhone = $user->phone;
            } else {
                $pemohonProfile = $permohonan->pemohon1;
                if ($pemohonProfile && !empty($pemohonProfile->phone)) {
                    $targetPhone = $pemohonProfile->phone;
                }
            }

            if ($targetPhone) {
                $name = $user ? $user->name : ($pemohonProfile ? $pemohonProfile->name : 'Pemohon');
                $personalMsg = str_replace("Yth. Pemohon Kerja Sama,", "Yth. Bpk/Ibu *$name*,", $formalMsg);
                $wa->sendMessage($targetPhone, $personalMsg);
            }

            // 2. Notify Group (Admin) - INTERNAL
            $adminMsg = "*INFO ADMIN - SIKERJA*\n" .
                "Pembahasan Selesai\n\n" .
                "Instansi: {$permohonan->nama_instansi}\n" .
                "Perihal: {$permohonan->label}\n" .
                "Status: *Menunggu Penjadwalan*\n" .
                "Operator: " . Auth::user()->name . "\n" .
                "Waktu: " . now()->format('d M Y H:i') . "\n\n" .
                "_Mohon monitor dashboard._";

            $group = env('WA_GROUP_ID', '120363189423910876@g.us');
            if ($group) {
                $wa->sendMessage($group, $adminMsg);
            }
        } catch (\Exception $e) {
            \Log::error("Failed to send WA Notification: " . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Pembahasan selesai. Pemohon dapat membuat jadwal pertemuan.');
    }
}
