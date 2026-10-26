<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\GaleriTkksd;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GaleriTkksdController extends Controller
{
    protected $title;
    protected $prefix;
    protected $view;
    protected $share;

    public function __construct()
    {
        $this->title = 'Galeri Kegiatan TKKSD';
        $this->view = 'Backend/Master/Galeri';
        $this->prefix = 'master.galeri-tkksd';

        $this->share = [
            'title' => $this->title,
            'view' => $this->view,
            'prefix' => $this->prefix
        ];
    }

    public function index(Request $request)
    {
        $query = GaleriTkksd::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('lokasi', 'like', "%{$search}%")
                  ->orWhere('kategori_kegiatan', 'like', "%{$search}%");
            });
        }

        $perPage = $request->input('per_page', 10);
        $datas = $query->orderBy('tanggal_kegiatan', 'desc')
            ->orderBy('order', 'asc')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render("$this->view/Index", [
            'datas' => $datas,
            'galeris' => $datas,
            'share' => $this->share,
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'tanggal_kegiatan' => 'nullable|date',
            'kategori_kegiatan' => 'nullable|string|max:100',
            'lokasi' => 'nullable|string|max:255',
            'order' => 'nullable|integer',
            'is_active' => 'nullable',
            'foto' => 'required|image|mimes:jpeg,png,jpg,webp,svg,gif|max:10240',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = time() . '_' . Str::slug($request->judul) . '.' . $file->getClientOriginalExtension();
            
            $destinationDir = public_path('uploads/galeri_tkksd');
            if (!File::isDirectory($destinationDir)) {
                File::makeDirectory($destinationDir, 0755, true, true);
            }
            $file->move($destinationDir, $filename);
            $fotoPath = 'uploads/galeri_tkksd/' . $filename;

            $cpanelUpload = '/home/sikerjav2samarin/public_html/uploads/galeri_tkksd';
            if (File::isDirectory($cpanelUpload)) {
                File::copy($destinationDir . '/' . $filename, $cpanelUpload . '/' . $filename);
            }
        }

        GaleriTkksd::create([
            'judul' => $validated['judul'],
            'deskripsi' => $request->deskripsi,
            'tanggal_kegiatan' => $request->tanggal_kegiatan ?? now()->toDateString(),
            'kategori_kegiatan' => $request->kategori_kegiatan ?? 'Rapat Pembahasan',
            'lokasi' => $request->lokasi ?? 'Kota Samarinda',
            'order' => $request->order ?? 0,
            'is_active' => $request->boolean('is_active', true),
            'foto' => $fotoPath,
        ]);

        return redirect()->back()->with('success', 'Foto kegiatan TKKSD berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $galeri = GaleriTkksd::findOrFail($id);

        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'tanggal_kegiatan' => 'nullable|date',
            'kategori_kegiatan' => 'nullable|string|max:100',
            'lokasi' => 'nullable|string|max:255',
            'order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $fotoPath = $galeri->foto;
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = time() . '_' . Str::slug($request->judul) . '.' . $file->getClientOriginalExtension();
            
            $destinationDir = public_path('uploads/galeri_tkksd');
            if (!File::isDirectory($destinationDir)) {
                File::makeDirectory($destinationDir, 0755, true, true);
            }
            $file->move($destinationDir, $filename);
            $fotoPath = 'uploads/galeri_tkksd/' . $filename;

            $cpanelUpload = '/home/sikerjav2samarin/public_html/uploads/galeri_tkksd';
            if (File::isDirectory($cpanelUpload)) {
                File::copy($destinationDir . '/' . $filename, $cpanelUpload . '/' . $filename);
            }
        }

        $galeri->update([
            'judul' => $validated['judul'],
            'deskripsi' => $request->deskripsi,
            'tanggal_kegiatan' => $request->tanggal_kegiatan ?? $galeri->tanggal_kegiatan,
            'kategori_kegiatan' => $request->kategori_kegiatan ?? $galeri->kategori_kegiatan,
            'lokasi' => $request->lokasi ?? $galeri->lokasi,
            'order' => $request->order ?? 0,
            'is_active' => $request->boolean('is_active', true),
            'foto' => $fotoPath,
        ]);

        return redirect()->back()->with('success', 'Foto kegiatan TKKSD berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $galeri = GaleriTkksd::findOrFail($id);
        $galeri->delete();

        return redirect()->back()->with('success', 'Foto kegiatan TKKSD berhasil dihapus.');
    }
}
