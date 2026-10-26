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
            
            try {
                // 1. Simpan ke public storage disk
                $file->storeAs('uploads/galeri_tkksd', $filename, 'public');
                $sourceFile = storage_path('app/public/uploads/galeri_tkksd/' . $filename);

                // 2. Mirror ke public_html dan public
                $destPublic = public_path('uploads/galeri_tkksd');
                if (!File::isDirectory($destPublic)) {
                    @File::makeDirectory($destPublic, 0777, true, true);
                }
                $destCpanel = '/home/sikerjav2samarin/public_html/uploads/galeri_tkksd';
                if (!File::isDirectory($destCpanel)) {
                    @File::makeDirectory($destCpanel, 0777, true, true);
                }

                if (file_exists($sourceFile)) {
                    @copy($sourceFile, $destPublic . '/' . $filename);
                    @copy($sourceFile, $destCpanel . '/' . $filename);
                }
            } catch (\Exception $e) {
                \Log::warning("Galeri foto upload warning: " . $e->getMessage());
            }

            $fotoPath = 'uploads/galeri_tkksd/' . $filename;
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
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        $fotoPath = $galeri->foto;
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = time() . '_' . Str::slug($request->judul) . '.' . $file->getClientOriginalExtension();
            
            try {
                // 1. Simpan ke public storage disk
                $file->storeAs('uploads/galeri_tkksd', $filename, 'public');
                $sourceFile = storage_path('app/public/uploads/galeri_tkksd/' . $filename);

                // 2. Mirror ke public_html dan public
                $destPublic = public_path('uploads/galeri_tkksd');
                if (!File::isDirectory($destPublic)) {
                    @File::makeDirectory($destPublic, 0777, true, true);
                }
                $destCpanel = '/home/sikerjav2samarin/public_html/uploads/galeri_tkksd';
                if (!File::isDirectory($destCpanel)) {
                    @File::makeDirectory($destCpanel, 0777, true, true);
                }

                if (file_exists($sourceFile)) {
                    @copy($sourceFile, $destPublic . '/' . $filename);
                    @copy($sourceFile, $destCpanel . '/' . $filename);
                }
            } catch (\Exception $e) {
                \Log::warning("Galeri foto upload warning: " . $e->getMessage());
            }

            $fotoPath = 'uploads/galeri_tkksd/' . $filename;
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
        if ($galeri->foto) {
            @unlink(public_path($galeri->foto));
            @unlink(storage_path('app/public/' . $galeri->foto));
            @unlink('/home/sikerjav2samarin/public_html/' . $galeri->foto);
        }
        $galeri->delete();

        return redirect()->back()->with('success', 'Foto kegiatan TKKSD berhasil dihapus.');
    }
}
