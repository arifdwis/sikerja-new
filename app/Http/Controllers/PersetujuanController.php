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

class PersetujuanController extends Controller implements HasMiddleware
{
    protected $title;
    protected $prefix;
    protected $view;
    protected $share;

    public function __construct()
    {
        $this->title = 'Persetujuan Permohonan';
        $this->view = 'Backend/Persetujuan';
        $this->prefix = 'persetujuan';

        $this->share = [
            'title' => $this->title,
            'view' => $this->view,
            'prefix' => $this->prefix
        ];
    }

    public static function middleware(): array
    {
        return [
            new Middleware('can:permohonan.menu.persetujuan', only: ['index', 'update']),
        ];
    }

    public function index(Request $request)
    {
        $query = Permohonan::with([
            'kategori',
            'pemohon1',
            'operator.corporate',
            'provinsi',
            'kota',
            'penjadwalans' => function ($q) {
                $q->latest();
            }
        ])
            ->where('status', Permohonan::STATUS_PENJADWALAN) // Status 2: Menunggu Persetujuan Jadwal
            ->latest();

        if ($request->has('kategori') && $request->kategori) {
            $query->where('id_kategori', $request->kategori);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('label', 'like', "%{$search}%")
                    ->orWhere('nomor_permohonan', 'like', "%{$search}%")
                    ->orWhere('nama_instansi', 'like', "%{$search}%");
            });
        }

        $datas = $query->paginate(10)->withQueryString();
        $kategoris = \App\Models\Kategori::all();

        return Inertia::render("$this->view/Index", [
            'datas' => $datas,
            'kategoris' => $kategoris,
            'share' => $this->share,
            'filters' => $request->only(['search', 'kategori']),
        ]);
    }

    public function show(string $uuid)
    {
        $permohonan = Permohonan::with(['kategori', 'pemohon1', 'operator.corporate', 'provinsi', 'kota', 'files', 'penjadwalans'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        return response()->json($permohonan);
    }

    public function update(Request $request, string $uuid)
    {
        $permohonan = Permohonan::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'status' => 'required|in:1,9', // 1: Approve, 9: Reject
            'admin_comment' => 'nullable|string',
        ]);

        // Find pending schedule
        $pendingJadwal = $permohonan->penjadwalans()->where('status', 0)->latest()->first();

        // Send WhatsApp Notification
        try {
            $wa = app(WhatsappService::class);
            $statusPart = $validated['status'] == 9 ? '*Jadwal Ditolak (Perlu Jadwal Baru)*' : '*Selesai (Jadwal Disetujui)*';
            $descPart = $validated['status'] == 9 ? 'Jadwal ditolak. Silakan ajukan jadwal baru.' : 'Jadwal disetujui. Proses selesai.';

            // 1. Notify User (Pemohon) - FORMAL
            $formalMsg = "*SIKERJA - PEMBARUAN STATUS*\n\n" .
                "Yth. Pemohon Kerja Sama,\n" .
                "Berikut kami sampaikan status terbaru permohonan Anda:\n\n" .
                "Instansi: *{$permohonan->nama_instansi}*\n" .
                "Perihal: {$permohonan->label}\n" .
                "Status: {$statusPart}\n" .
                "Catatan: " . ($validated['admin_comment'] ?? $descPart) . "\n\n" .
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
                "Persetujuan Jadwal\n\n" .
                "Instansi: {$permohonan->nama_instansi}\n" .
                "Status: {$statusPart}\n" .
                "Oleh: " . Auth::user()->name . "\n" .
                "Waktu: " . now()->format('d M Y H:i') . "\n\n" .
                "_Mohon monitor dashboard._";

            $group = env('WA_GROUP_ID', '120363189423910876@g.us');
            if ($group) {
                $wa->sendMessage($group, $adminMsg);
            }
        } catch (\Exception $e) {
            \Log::error("Failed to send WA Notification: " . $e->getMessage());
        }

        if ($validated['status'] == 9) {
            // REJECT
            if ($pendingJadwal) {
                $pendingJadwal->update([
                    'status' => 2, // Ditolak
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                    'admin_comment' => $validated['admin_comment'] ?? 'Ditolak melalui menu Persetujuan',
                ]);
                return redirect()->back()->with('success', 'Jadwal ditolak. Menunggu jadwal baru.');
            }
            return redirect()->back()->with('error', 'Tidak ada jadwal yang perlu ditolak.');

        } else {
            // APPROVE
            if ($pendingJadwal) {
                $pendingJadwal->update([
                    'status' => 1, // Disetujui
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                    'admin_comment' => $validated['admin_comment'] ?? 'Disetujui melalui menu Persetujuan',
                ]);

                $permohonan->update([
                    'status' => Permohonan::STATUS_SELESAI, // Status 4: Selesai
                ]);

                // Send Monev reminder to pemohon
                try {
                    if ($targetPhone) {
                        $monevMsg = "📋 *PENGINGAT MONEV*\n\n";
                        $monevMsg .= "Kerjasama *{$permohonan->label}* telah selesai.\n\n";
                        $monevMsg .= "Silakan isi Form Monitoring & Evaluasi (Monev) melalui menu MONEV di SIKERJA untuk memberikan feedback pelaksanaan kerjasama.\n\n";
                        $monevMsg .= "_Terima kasih atas kerjasamanya._";
                        $wa->sendMessage($targetPhone, $monevMsg);
                    }
                } catch (\Exception $e) {
                    \Log::error('Monev reminder error: ' . $e->getMessage());
                }

                return redirect()->back()->with('success', 'Jadwal disetujui. Permohonan kerjasama selesai.');
            }
            return redirect()->back()->with('error', 'Tidak ada jadwal yang perlu disetujui.');
        }
    }
}
