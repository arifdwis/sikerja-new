<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class PermissionController extends Controller implements HasMiddleware
{
    protected $title;
    protected $prefix;
    protected $view;
    protected $data;
    protected $share;

    public function __construct(Permission $data)
    {
        $this->title = 'Permission';
        $this->view = 'Backend/Settings/Permissions';
        $this->prefix = 'settings.permissions';
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
            new Middleware('can:settings.permissions.index', only: ['index']),
            new Middleware('can:settings.permissions.create', only: ['create', 'store']),
            new Middleware('can:settings.permissions.edit', only: ['edit', 'update']),
            new Middleware('can:settings.permissions.destroy', only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = $this->data::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        // Return 'datas' (paginator) and 'share'
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
            'name' => 'required|string|max:50|unique:permissions',
        ]);

        $this->data::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
        ]);

        return redirect()->route("$this->prefix.index")
            ->with('success', 'Permission berhasil ditambahkan.');
    }

    // ...

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $permission = $this->data::findOrFail($id);
        return Inertia::render("$this->view/Edit", [
            'permission' => $permission,
            'share' => $this->share
        ]);
    }

    public function update(Request $request, $id)
    {
        $permission = $this->data::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('permissions')->ignore($permission->id)],
        ]);

        $permission->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
        ]);

        return redirect()->route("$this->prefix.index")
            ->with('success', 'Permission berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $permission = $this->data::findOrFail($id);
        $permission->delete();

        return redirect()->route("$this->prefix.index")
            ->with('success', 'Permission berhasil dihapus.');
    }
}
