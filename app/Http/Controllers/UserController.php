<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class UserController extends Controller implements HasMiddleware
{
    protected $title;
    protected $prefix;
    protected $view;
    protected $data;
    protected $share;

    public function __construct(User $data)
    {
        $this->title = 'Users';
        $this->view = 'Backend/Settings/Users';
        $this->prefix = 'settings.users';
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
            new Middleware('can:settings.users.index', only: ['index']),
            new Middleware('can:settings.users.create', only: ['create', 'store']),
            new Middleware('can:settings.users.edit', only: ['edit', 'update']),
            new Middleware('can:settings.users.destroy', only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = $this->data::query()->with(['roles']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('sso') && $request->sso !== null) {
            $isSso = filter_var($request->sso, FILTER_VALIDATE_BOOLEAN);
            if ($isSso) {
                $query->whereNotNull('uid');
            } else {
                $query->whereNull('uid');
            }
        }

        if ($request->has('verified') && $request->verified !== null) {
            $isVerified = filter_var($request->verified, FILTER_VALIDATE_BOOLEAN);
            if ($isVerified) {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
            }
        }

        $datas = $query->paginate(10)->withQueryString();

        return Inertia::render("$this->view/Index", [
            'users' => $datas,
            'datas' => $datas,
            'roles' => Role::all(),
            'opds' => \App\Models\Opd::active()->orderBy('nama')->get(['id', 'nama', 'singkatan']),
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
            'roles' => Role::orderBy('name')->get(),
            'opds' => \App\Models\Opd::active()->orderBy('nama')->get(['id', 'nama', 'singkatan']),
            'share' => $this->share,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'role_id' => 'required|exists:roles,id',
            // id_opd wajib jika role adalah tkksd_lokus
            'id_opd' => 'nullable|exists:opd,id',
        ];

        $validated = $request->validate($rules);

        // Cek role tkksd_lokus → wajib id_opd
        $role = Role::findOrFail($validated['role_id']);
        if ($role->slug === 'tkksd_lokus' && empty($validated['id_opd'])) {
            return back()->withErrors(['id_opd' => 'OPD wajib dipilih untuk role TKKSD Lokus Kerjasama.'])->withInput();
        }

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'id_opd' => $validated['id_opd'] ?? null,
            // Semua user pakai SSO Samarinda. Password di-generate random
            // sebagai dummy karena column NOT NULL.
            'password' => Hash::make(Str::random(32)),
        ];

        // Optional: jika user dari pencarian SSO, attach uid
        if ($request->filled('uid')) {
            $userData['uid'] = $request->uid;
        }

        $user = $this->data::create($userData);

        // Pakai method assignRole custom dari User model (bukan Spatie)
        $user->assignRole($role->slug);

        return redirect()->route("$this->prefix.index")
            ->with('success', 'User berhasil ditambahkan.');
    }

    /**
     * Search user from SSO Server
     */
    public function searchSso(Request $request)
    {
        $query = $request->input('query');
        if (strlen($query) < 3)
            return response()->json([]);

        try {
            // URL fix dari source code legacy
            $ssoUrl = 'https://sso.samarindakota.go.id/api/sso/findUser';

            // Legacy code menggunakan POST dan parameter 'search'
            $response = Http::asForm()->post($ssoUrl, [
                'search' => $query
            ]);

            if ($response->successful()) {
                // Return raw response. Frontend harus adaptasi dengan struktur data ini.
                // Legacy select2 mengharapkan array objects [{id:..., text:...}]
                return response()->json($response->json());
            }
        } catch (\Exception $e) {
            // Log::error('SSO Search Error: ' . $e->getMessage());
        }

        return response()->json([]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $user = $this->data::with('roles')->findOrFail($id);

        return Inertia::render("$this->view/Edit", [
            'user' => $user,
            'roles' => Role::orderBy('name')->get(),
            'opds' => \App\Models\Opd::active()->orderBy('nama')->get(['id', 'nama', 'singkatan']),
            'share' => $this->share,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = $this->data::findOrFail($id);

        $rules = [
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role_id' => 'required|exists:roles,id',
            'id_opd' => 'nullable|exists:opd,id',
        ];

        $validated = $request->validate($rules);

        // Cek role tkksd_lokus → wajib id_opd
        $role = Role::findOrFail($validated['role_id']);
        if ($role->slug === 'tkksd_lokus' && empty($validated['id_opd'])) {
            return back()->withErrors(['id_opd' => 'OPD wajib dipilih untuk role TKKSD Lokus Kerjasama.'])->withInput();
        }

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'id_opd' => $validated['id_opd'] ?? null,
        ]);

        $user->save();

        // Sync role: detach all then attach the selected one
        $user->roles()->sync([$role->id]);

        return redirect()->route("$this->prefix.index")
            ->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = $this->data::findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        return redirect()->route("$this->prefix.index")
            ->with('success', 'User berhasil dihapus.');
    }
}
