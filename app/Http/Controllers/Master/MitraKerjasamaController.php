<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\KategoriMitra;
use App\Models\MitraKerjasama;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MitraKerjasamaController extends Controller
{
    protected $title;
    protected $prefix;
    protected $view;
    protected $share;

    public function __construct()
    {
        $this->title = 'Mitra Kerja Sama';
        $this->view = 'Backend/Master/Mitra';
        $this->prefix = 'master.mitra';

        $this->share = [
            'title' => $this->title,
            'view' => $this->view,
            'prefix' => $this->prefix
        ];
    }

    public function index(Request $request)
    {
        $query = MitraKerjasama::with('kategori');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('nama', 'like', "%{$search}%");
        }

        if ($request->filled('kategori_id')) {
            $query->where('kategori_mitra_id', $request->kategori_id);
        }

        $mitras = $query->orderBy('kategori_mitra_id')
            ->orderBy('order')
            ->paginate(15)
            ->withQueryString();

        $kategoris = KategoriMitra::withCount('mitras')->orderBy('order')->get();

        return Inertia::render("$this->view/Index", [
            'mitras' => $mitras,
            'kategoris' => $kategoris,
            'share' => $this->share,
            'filters' => $request->only(['search', 'kategori_id']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kategori_mitra_id' => 'required|exists:kategori_mitra,id',
            'url_website' => 'nullable|url|max:255',
            'deskripsi' => 'nullable|string',
            'order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = time() . '_' . Str::slug($request->nama) . '.' . $file->getClientOriginalExtension();
            
            // Save to public storage and copy to public_html if exists
            $destinationDir = public_path('uploads/mitra');
            if (!File::isDirectory($destinationDir)) {
                File::makeDirectory($destinationDir, 0755, true, true);
            }
            $file->move($destinationDir, $filename);
            $logoPath = 'uploads/mitra/' . $filename;

            // Optional mirror to /home/sikerjav2samarin/public_html/uploads/mitra/
            $cpanelUpload = '/home/sikerjav2samarin/public_html/uploads/mitra';
            if (File::isDirectory($cpanelUpload)) {
                File::copy($destinationDir . '/' . $filename, $cpanelUpload . '/' . $filename);
            }
        }

        MitraKerjasama::create([
            'nama' => $validated['nama'],
            'kategori_mitra_id' => $validated['kategori_mitra_id'],
            'url_website' => $request->url_website,
            'deskripsi' => $request->deskripsi,
            'order' => $request->order ?? 0,
            'is_active' => $request->boolean('is_active', true),
            'logo' => $logoPath,
        ]);

        return redirect()->back()->with('success', 'Mitra kerja sama berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $mitra = MitraKerjasama::findOrFail($id);

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kategori_mitra_id' => 'required|exists:kategori_mitra,id',
            'url_website' => 'nullable|url|max:255',
            'deskripsi' => 'nullable|string',
            'order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
        ]);

        $logoPath = $mitra->logo;
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = time() . '_' . Str::slug($request->nama) . '.' . $file->getClientOriginalExtension();
            
            $destinationDir = public_path('uploads/mitra');
            if (!File::isDirectory($destinationDir)) {
                File::makeDirectory($destinationDir, 0755, true, true);
            }
            $file->move($destinationDir, $filename);
            $logoPath = 'uploads/mitra/' . $filename;

            $cpanelUpload = '/home/sikerjav2samarin/public_html/uploads/mitra';
            if (File::isDirectory($cpanelUpload)) {
                File::copy($destinationDir . '/' . $filename, $cpanelUpload . '/' . $filename);
            }
        }

        $mitra->update([
            'nama' => $validated['nama'],
            'kategori_mitra_id' => $validated['kategori_mitra_id'],
            'url_website' => $request->url_website,
            'deskripsi' => $request->deskripsi,
            'order' => $request->order ?? 0,
            'is_active' => $request->boolean('is_active', true),
            'logo' => $logoPath,
        ]);

        return redirect()->back()->with('success', 'Mitra kerja sama berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $mitra = MitraKerjasama::findOrFail($id);
        $mitra->delete();

        return redirect()->back()->with('success', 'Mitra kerja sama berhasil dihapus.');
    }

    // CRUD Kategori Mitra
    public function storeKategori(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255|unique:kategori_mitra,nama',
            'deskripsi' => 'nullable|string',
            'order' => 'nullable|integer',
        ]);

        KategoriMitra::create([
            'nama' => $request->nama,
            'slug' => Str::slug($request->nama),
            'deskripsi' => $request->deskripsi,
            'order' => $request->order ?? 0,
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Kategori mitra berhasil ditambahkan.');
    }

    public function updateKategori(Request $request, $id)
    {
        $kategori = KategoriMitra::findOrFail($id);
        
        $request->validate([
            'nama' => 'required|string|max:255|unique:kategori_mitra,nama,' . $id,
            'deskripsi' => 'nullable|string',
            'order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $kategori->update([
            'nama' => $request->nama,
            'slug' => Str::slug($request->nama),
            'deskripsi' => $request->deskripsi,
            'order' => $request->order ?? $kategori->order,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->back()->with('success', 'Kategori mitra berhasil diperbarui.');
    }

    public function destroyKategori($id)
    {
        $kategori = KategoriMitra::findOrFail($id);
        if ($kategori->mitras()->count() > 0) {
            return redirect()->back()->with('error', 'Kategori tidak dapat dihapus karena masih memiliki mitra terkait.');
        }

        $kategori->delete();
        return redirect()->back()->with('success', 'Kategori mitra berhasil dihapus.');
    }
}
