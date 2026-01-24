<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class KategoriController extends Controller implements HasMiddleware
{
    protected $title;
    protected $prefix;
    protected $view;
    protected $data;
    protected $share;

    public function __construct(Kategori $data)
    {
        $this->title = 'Kategori Kerjasama';
        $this->view = 'Backend/Master/Kategori';
        $this->prefix = 'master.kategori';
        $this->data = $data;

        $this->share = [
            'title' => $this->title,
            'view' => $this->view,
            'prefix' => $this->prefix
        ];
    }

    public static function middleware(): array
    {
        return [
            new Middleware('can:master.kategori.index', only: ['index']),
            new Middleware('can:master.kategori.create', only: ['create', 'store']),
            new Middleware('can:master.kategori.edit', only: ['edit', 'update']),
            new Middleware('can:master.kategori.destroy', only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = $this->data::withCount('permohonans');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('label', 'like', "%{$search}%");
        }

        $datas = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render("$this->view/Index", [
            'datas' => $datas,
            'share' => $this->share,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render("$this->view/Create", [
            'share' => $this->share,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255|unique:kategori',
        ]);

        $this->data::create($validated);

        return redirect()->route("$this->prefix.index")
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($uuid)
    {
        $kategori = $this->data::uuid($uuid)->firstOrFail();

        return Inertia::render("$this->view/Edit", [
            'kategori' => $kategori,
            'share' => $this->share,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $uuid)
    {
        $kategori = $this->data::uuid($uuid)->firstOrFail();

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:255', Rule::unique('kategori')->ignore($kategori->id)],
        ]);

        $kategori->update([
            'label' => $validated['label'],
            'slug' => Str::slug($validated['label']),
        ]);

        return redirect()->route("$this->prefix.index")
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($uuid)
    {
        $kategori = $this->data::uuid($uuid)->firstOrFail();

        if ($kategori->permohonans()->count() > 0) {
            return back()->with('error', 'Kategori masih digunakan, tidak dapat dihapus.');
        }

        $kategori->delete();

        return redirect()->route("$this->prefix.index")
            ->with('success', 'Kategori berhasil dihapus.');
    }
}
