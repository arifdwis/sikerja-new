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
            // Persetujuan: status 2 (Menunggu Jadwal Penandatanganan)
            // Admin review jadwal yang diajukan pemohon, lalu setujui/tolak
            ->where('status', Permohonan::STATUS_PENJADWALAN)
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

        if ($validated['status'] == 9) {
            // REJECT — kirim notif formal ke pemohon (jadwal ditolak, ajukan baru)
            if ($pendingJadwal) {
                $pendingJadwal->update([
                    'status' => 2, // Ditolak
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                    'admin_comment' => $validated['admin_comment'] ?? 'Ditolak melalui menu Persetujuan',
                ]);

                $this->notifyPemohonJadwalDitolak($permohonan, $validated['admin_comment'] ?? 'Mohon mengajukan jadwal baru sesuai ketentuan.');

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

                // Status berubah ke JADWAL_DISETUJUI (3) — observer kirim notif formal ke pemohon
                // (template: jadwalDisetujui via PermohonanObserver pada status change)
                $permohonan->update([
                    'status' => Permohonan::STATUS_JADWAL_DISETUJUI,
                ]);

                // Tambahan: notif khusus dengan tanggal jadwal yang disetujui
                $this->notifyPemohonJadwalDisetujui($permohonan, $pendingJadwal);

                return redirect()->back()->with('success', 'Jadwal disetujui. Pemohon dapat mengupload PKS final.');
            }
            return redirect()->back()->with('error', 'Tidak ada jadwal yang perlu disetujui.');
        }
    }

    private function notifyPemohonJadwalDitolak(Permohonan $permohonan, string $alasan): void
    {
        $user = User::find($permohonan->id_pemohon_0);
        $pemohonProfile = $permohonan->pemohon1;
        $name = $pemohonProfile?->name ?: ($user?->name ?: 'Pemohon');

        $payload = \App\Services\NotificationTemplate::statusUpdate(
            $name,
            $permohonan,
            'Jadwal Penandatanganan Belum Disetujui',
            $alasan
        );

        \App\Models\Notifikasi::create([
            'id_user'       => $permohonan->id_pemohon_0,
            'id_permohonan' => $permohonan->id,
            'from_user_id'  => Auth::id(),
            'type'          => 'jadwal_rejected',
            'title'         => 'Jadwal Penandatanganan Belum Disetujui',
            'message'       => $payload['system']['message'],
            'data'          => json_encode(['uuid' => $permohonan->uuid]),
            'is_read'       => false,
        ]);

        try {
            $wa = app(WhatsappService::class);
            $phone = $user?->phone ?: $pemohonProfile?->phone;
            if ($phone) {
                $wa->sendMessage($phone, $payload['wa']);
            }
        } catch (\Throwable $e) {
            \Log::error('Notif jadwal rejected: ' . $e->getMessage());
        }
    }

    private function notifyPemohonJadwalDisetujui(Permohonan $permohonan, $jadwal): void
    {
        $user = User::find($permohonan->id_pemohon_0);
        $pemohonProfile = $permohonan->pemohon1;
        $name = $pemohonProfile?->name ?: ($user?->name ?: 'Pemohon');

        $tanggalJadwal = $jadwal->tanggal
            ? \Carbon\Carbon::parse($jadwal->tanggal)->translatedFormat('l, d F Y')
            : '(belum ditetapkan)';

        $payload = \App\Services\NotificationTemplate::jadwalDisetujui($name, $permohonan, $tanggalJadwal);

        \App\Models\Notifikasi::create([
            'id_user'       => $permohonan->id_pemohon_0,
            'id_permohonan' => $permohonan->id,
            'from_user_id'  => Auth::id(),
            'type'          => 'jadwal_approved',
            'title'         => $payload['system']['title'],
            'message'       => $payload['system']['message'],
            'data'          => json_encode(['uuid' => $permohonan->uuid]),
            'is_read'       => false,
        ]);

        try {
            $wa = app(WhatsappService::class);
            $phone = $user?->phone ?: $pemohonProfile?->phone;
            if ($phone) {
                $wa->sendMessage($phone, $payload['wa']);
            }
        } catch (\Throwable $e) {
            \Log::error('Notif jadwal approved: ' . $e->getMessage());
        }
    }
}
