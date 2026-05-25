<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

/**
 * Controller khusus halaman TKKSD Lokus untuk daftar kerjasama
 * yang melibatkan OPD-nya. Hanya akses read-only.
 */
class TkksdLokusController extends Controller
{
    /**
     * Daftar kerjasama OPD TKKSD Lokus berdasarkan status.
     *
     * @param string $tipe 'aktif' (Pelaksanaan) atau 'selesai' (Selesai)
     */
    public function kerjasama(Request $request, string $tipe = 'aktif')
    {
        $user = Auth::user();

        if (!$user->hasRole('tkksd_lokus')) {
            abort(403, 'Hanya TKKSD Lokus yang dapat mengakses halaman ini.');
        }

        $opdId = $user->id_opd;
        if (!$opdId) {
            abort(403, 'OPD belum diatur pada akun Anda. Hubungi admin.');
        }

        $statusMap = [
            'aktif'   => Permohonan::STATUS_PELAKSANAAN,
            'selesai' => Permohonan::STATUS_SELESAI,
        ];

        if (!isset($statusMap[$tipe])) {
            abort(404);
        }

        $title = $tipe === 'aktif' ? 'Kerjasama Aktif' : 'Kerjasama Selesai';

        // Base query untuk OPD user.
        $base = Permohonan::query()
            ->whereHas('opds', fn($q) => $q->where('opd.id', $opdId));

        // Listing
        $query = $base->with([
                'kategori',
                'operator',
                'kota',
                'opds',
                'files',
                'penjadwalans' => fn($q) => $q->latest(),
            ])
            ->where('status', $statusMap[$tipe])
            ->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('label', 'like', "%{$search}%")
                    ->orWhere('nama_instansi', 'like', "%{$search}%")
                    ->orWhere('nomor_permohonan', 'like', "%{$search}%");
            });
        }

        $permohonan = $query->paginate(15)->withQueryString();

        return Inertia::render('Backend/TkksdLokus/Kerjasama', [
            'permohonan' => $permohonan,
            'tipe'       => $tipe,
            'share' => [
                'title'  => $title,
                'view'   => 'Backend/TkksdLokus',
                'prefix' => 'tkksd-lokus.kerjasama',
            ],
            'filters' => $request->only(['search', 'detail']),
        ]);
    }
}
