<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Pemohon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class PemohonController extends Controller implements HasMiddleware
{
    protected $title;
    protected $prefix;
    protected $view;
    protected $data;
    protected $share;

    public function __construct(Pemohon $data)
    {
        $this->title = 'Data Pemohon';
        $this->view = 'Backend/Master/Pemohon';
        $this->prefix = 'master.pemohon';
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
            new Middleware('can:master.pemohon.index', only: ['index']),
            new Middleware('can:master.pemohon.create', only: ['create', 'store']),
            new Middleware('can:master.pemohon.edit', only: ['edit', 'update']),
            new Middleware('can:master.pemohon.destroy', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $query = $this->data::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('instansi', 'like', "%{$search}%")
                    ->orWhere('unit_kerja', 'like', "%{$search}%")
                    ->orWhere('nama_instansi', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $datas = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render("$this->view/Index", [
            'datas' => $datas,
            'share' => $this->share,
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'instansi' => 'required|string|max:255',
            'jabatan' => 'nullable|string|max:255',
            'email' => 'required|email|unique:pemohon',
            'phone' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
        ]);

        $validated['uuid'] = Str::uuid();
        $this->data::create($validated);

        return redirect()->route("$this->prefix.index")
            ->with('success', 'Pemohon berhasil ditambahkan.');
    }

    public function update(Request $request, $uuid)
    {
        $pemohon = $this->data::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'instansi' => 'required|string|max:255',
            'jabatan' => 'nullable|string|max:255',
            'email' => ['required', 'email', Rule::unique('pemohon')->ignore($pemohon->id)],
            'phone' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
        ]);

        $pemohon->update($validated);

        return redirect()->route("$this->prefix.index")
            ->with('success', 'Pemohon berhasil diperbarui.');
    }

    public function destroy($uuid)
    {
        $pemohon = $this->data::where('uuid', $uuid)->firstOrFail();
        $pemohon->delete();

        return redirect()->route("$this->prefix.index")
            ->with('success', 'Pemohon berhasil dihapus.');
    }
}
