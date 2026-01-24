<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class RoleController extends Controller implements HasMiddleware
{
    protected $title;
    protected $prefix;
    protected $view;
    protected $data;
    protected $share;

    public function __construct(Role $data)
    {
        $this->title = 'Role';
        $this->view = 'Backend/Settings/Roles';
        $this->prefix = 'settings.roles';
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
            new Middleware('can:settings.roles.index', only: ['index']),
            new Middleware('can:settings.roles.create', only: ['create', 'store']),
            new Middleware('can:settings.roles.edit', only: ['edit', 'update']),
            new Middleware('can:settings.roles.destroy', only: ['destroy']),
            new Middleware('can:settings.roles.permission', only: ['permission']),
            new Middleware('can:settings.roles.permission.update', only: ['updatePermission']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Gunakan client-side pagination approach jika ingin mengikuti contoh user (User bilang '$datas = $query->get()'),
        // TAPI Index.vue yang saya buat mengharapkan Paginator object.
        // User request: "$query = $this->data::query(); ... return compact('datas', 'share')"
        // Tapi di Index.vue saya terima 'roles' dan datanya paginated.
        // Jika saya ganti jadi 'datas' (flat array), code table saya harus di client-side pagination.

        // Saya akan tetap gunakan pagination server-side tapi dikirim sebagai variable 'datas' agar konsisten dengan prop name di view SiCerdas.

        $query = $this->data::with(['permissions', 'users'])->withCount('users');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        // Jika mau client side pagination (get all), pakailah get().
        // Tapi roles bisa banyak. Saya akan tetap paginate untuk performa, tapi nama variable diganti 'datas'.
        // User memberikan contoh: "$datas = $query->orderBy('kode')->get();"
        // Saya akan ikuti "datas = all" agar Index.vue versi SiCerdas (yang melakukan client sort/page) bekerja maksimal jika saya pakai sortable.
        // Tapi tadi saya hapus sortable. 
        // Index.vue yang SAYA buat tadi (Roles/Index.vue) menggunakan Server Side Pagination (prop: roles).

        // Agar aman: Index.vue saya sudah terlanjur dibuat dengan server-side.
        // Saya akan sesuaikan Controller ini agar mengirim variable 'roles' ATAU saya refactor Index.vue agar terima 'datas'.
        // Mengingat contoh user, dia return 'datas'.
        // Mari saya kirim 'datas' yang berupa paginator (karena inertia handle paginator as object too).

        $datas = $query->paginate(10)->withQueryString();
        $share = $this->share;

        // Mengirim sebagai 'datas' (sesuai contoh user) dan 'roles' (sesuai Index.vue saya sekarang).
        // Saya akan update Index.vue nanti agar terima props 'datas'.
        return Inertia::render("$this->view/Index", [
            'datas' => $datas, // Untuk konsistensi dengan contoh user
            'roles' => $datas, // Untuk kompatibilitas dengan Index.vue yang sudah saya tulis (biar gak error dulu)
            'share' => $share,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:roles',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = $this->data::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
        ]);

        if (isset($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }

        return redirect()->route("$this->prefix.index")
            ->with('success', 'Role berhasil ditambahkan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $role = $this->data::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('roles')->ignore($role->id)],
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
        ]);

        if (isset($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }

        return redirect()->route("$this->prefix.index")
            ->with('success', 'Role berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $role = $this->data::findOrFail($id);

        if ($role->slug === 'administrator' || $role->slug === 'superadmin') {
            return back()->with('error', 'Role Administrator/Superadmin tidak dapat dihapus.');
        }

        if ($role->users()->count() > 0) {
            return back()->with('error', 'Role masih digunakan oleh user, tidak dapat dihapus.');
        }

        $role->delete();

        return redirect()->route("$this->prefix.index")
            ->with('success', 'Role berhasil dihapus.');
    }

    /**
     * Show the form for managing permissions of the specified role.
     */
    public function permission($id)
    {
        $role = $this->data::with('permissions')->findOrFail($id);
        $allPermissions = Permission::all();
        $share = $this->share;

        // Group permissions
        $groupedPermissions = $allPermissions->groupBy(function ($item) {
            $parts = explode('.', $item->name);
            return count($parts) > 1 ? $parts[0] : 'Others';
        });

        return Inertia::render("$this->view/Permission", [
            'role' => $role,
            'permissions' => $groupedPermissions,
            'share' => $share
        ]);
    }

    /**
     * Update permissions for the specified role.
     */
    public function updatePermission(Request $request, $id)
    {
        $role = $this->data::findOrFail($id);

        $validated = $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        if (isset($validated['permissions'])) {
            // Find IDs by names because model is custom and frontend sends names
            $permissionIds = Permission::whereIn('name', $validated['permissions'])->pluck('id');
            $role->permissions()->sync($permissionIds);
        } else {
            $role->permissions()->sync([]);
        }

        return redirect()->route("$this->prefix.index")
            ->with('success', 'Hak akses berhasil diperbarui.');
    }

    // Middleware definition disabled mostly for now until 'can' middleware is fully implemented
    /*
    public static function middleware(): array
    {
        $can = 'roles';
        return [
            'auth',
            // new Middleware('can:'.$can.'_index', only : ['index']),
        ];
    }
    */
}
