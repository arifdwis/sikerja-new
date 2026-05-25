<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Opd;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class OpdController extends Controller implements HasMiddleware
{
    protected $share;

    public function __construct()
    {
        $this->share = [
            'title'  => 'Master OPD',
            'view'   => 'Backend/Master/Opd',
            'prefix' => 'master.opd',
        ];
    }

    public static function middleware(): array
    {
        return [
            new Middleware('role:administrator,superadmin'),
        ];
    }

    public function index(Request $request)
    {
        $query = Opd::query()->orderBy('nama');

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('singkatan', 'like', "%{$search}%");
            });
        }

        $datas = $query->paginate(20)->withQueryString();

        return Inertia::render('Backend/Master/Opd/Index', [
            'datas' => $datas,
            'share' => $this->share,
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:opd,nama',
            'singkatan' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;

        Opd::create($validated);

        return redirect()->route('master.opd.index')->with('success', 'OPD berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $opd = Opd::findOrFail($id);

        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:opd,nama,' . $opd->id,
            'singkatan' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
        ]);

        $opd->update($validated);

        return redirect()->route('master.opd.index')->with('success', 'OPD berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $opd = Opd::findOrFail($id);

        // Cek apakah masih ada permohonan/user yang terkait
        if ($opd->permohonans()->exists() || $opd->users()->exists() || $opd->kerjasamaManuals()->exists()) {
            return redirect()->back()->with('error', 'OPD masih digunakan, tidak dapat dihapus.');
        }

        $opd->delete();

        return redirect()->route('master.opd.index')->with('success', 'OPD berhasil dihapus.');
    }
}
