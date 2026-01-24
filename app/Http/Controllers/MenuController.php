<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class MenuController extends Controller implements HasMiddleware
{
    protected $title;
    protected $prefix;
    protected $view;
    protected $data;
    protected $share;
    protected $updateCount = 0;

    public function __construct(Menu $data)
    {
        $this->title = 'Menu Management';
        $this->view = 'Backend/Settings/Menus';
        $this->prefix = 'settings.menu';
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
            new Middleware('can:settings.menu.index', only: ['index', 'store', 'update', 'destroy', 'reorder']),
        ];
    }

    public function index(Request $request)
    {
        // Get all menus ordered
        $allMenus = $this->data::orderBy('order')->get();

        // Build TreeNodes for PrimeVue/NestedMenu
        $nodes = $this->buildTreeNodes($allMenus);

        return Inertia::render("$this->view/Index", [
            'nodes' => $nodes,
            'share' => $this->share,
            'roles' => Role::all(),
        ]);
    }

    protected function buildTreeNodes($menus, $parentId = null)
    {
        $branch = [];
        foreach ($menus as $menu) {
            if ($menu->parent_id == $parentId) {
                $children = $this->buildTreeNodes($menus, $menu->id);

                $node = [
                    'key' => (string) $menu->id,
                    'label' => $menu->title,
                    'data' => $menu,
                    'children' => $children,
                    'icon' => '' // Icon handled by template
                ];
                $branch[] = $node;
            }
        }
        return $branch;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'route' => 'nullable|string|max:100',
            'icon' => 'nullable|string|max:100',
            'roles' => 'nullable|string',
            'parent_id' => 'nullable|exists:menus,id',
            'is_active' => 'boolean'
        ]);

        $maxOrder = $this->data::where('parent_id', $request->parent_id)->max('order');

        $this->data::create([
            'parent_id' => $validated['parent_id'],
            'title' => $validated['title'],
            'route' => $validated['route'],
            'icon' => $validated['icon'],
            'roles' => $validated['roles'],
            'is_active' => $request->has('is_active') ? $validated['is_active'] : true,
            'order' => $maxOrder + 1,
        ]);

        return redirect()->back()->with('success', 'Menu berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $menu = $this->data::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'route' => 'nullable|string|max:100',
            'icon' => 'nullable|string|max:100',
            'roles' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $menu->update($validated);

        return redirect()->back()->with('success', 'Menu berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $menu = $this->data::findOrFail($id);
        $menu->delete(); // Cascade delete handled by DB

        return redirect()->back()->with('success', 'Menu berhasil dihapus.');
    }

    public function reorder(Request $request)
    {
        $items = $request->items;
        $this->updateCount = 0;

        if (empty($items)) {
            return response()->json(['message' => 'Tidak ada items untuk disimpan.'], 400);
        }

        DB::transaction(function () use ($items) {
            foreach ($items as $item) {
                Menu::where('id', $item['id'])->update([
                    'parent_id' => $item['parent_id'],
                    'order' => $item['order']
                ]);
                $this->updateCount++;
            }
        });

        return response()->json([
            'message' => 'Urutan menu berhasil disimpan.',
            'count' => $this->updateCount
        ]);
    }
}
