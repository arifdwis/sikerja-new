<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Laman;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class LamanController extends Controller implements HasMiddleware
{
    protected $title;
    protected $prefix;
    protected $view;
    protected $data;
    protected $share;

    public function __construct(Laman $data)
    {
        $this->title = 'Laman';
        $this->view = 'Backend/Master/Laman'; // We might need to create this view folder too, or reusing something? User didn't complain about view yet, usually standard Index.vue
        $this->prefix = 'master.laman';
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
            new Middleware('can:master.laman.index', only: ['index']),
            new Middleware('can:master.laman.create', only: ['create', 'store']),
            new Middleware('can:master.laman.edit', only: ['edit', 'update']),
            new Middleware('can:master.laman.destroy', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $query = $this->data::query();

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

    public function create()
    {
        return Inertia::render("$this->view/Create", [
            'share' => $this->share,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'integer',
        ]);

        $this->data::create($validated);

        return redirect()->route("$this->prefix.index")
            ->with('success', 'Laman berhasil ditambahkan.');
    }

    public function edit($uuid)
    {
        $laman = $this->data::uuid($uuid)->firstOrFail();

        return Inertia::render("$this->view/Edit", [
            'laman' => $laman,
            'share' => $this->share,
        ]);
    }

    public function update(Request $request, $uuid)
    {
        $laman = $this->data::uuid($uuid)->firstOrFail();

        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'integer',
        ]);

        $laman->update($validated);

        return redirect()->route("$this->prefix.index")
            ->with('success', 'Laman berhasil diperbarui.');
    }

    public function destroy($uuid)
    {
        $laman = $this->data::uuid($uuid)->firstOrFail();
        $laman->delete();

        return redirect()->route("$this->prefix.index")
            ->with('success', 'Laman berhasil dihapus.');
    }
}
