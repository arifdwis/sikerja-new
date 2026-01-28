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
        $userId = Auth::id();
        $query = Permohonan::with(['kategori', 'pemohon1', 'operator.corporate', 'provinsi', 'kota', 'files'])
            ->withExists([
                'historis as contributed' => function ($q) use ($userId) {
                    $q->where('id_operator', $userId);
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
                $q->where('id_operator', $userId);
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
                    $q->where('id_operator', $userId);
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
