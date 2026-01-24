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

        $datas = $query->paginate(10)->withQueryString();

        return Inertia::render("$this->view/Index", [
            'users' => $datas,
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
            'roles' => Role::all(),
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
        ];

        // Conditional validation for password: required only if NOT SSO user (no uid)
        if (!$request->filled('uid')) {
            $rules['password'] = ['required', 'confirmed', Rules\Password::defaults()];
        }

        $validated = $request->validate($rules);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        // Handle SSO User creation
        if ($request->filled('uid')) {
            $userData['uid'] = $request->uid;
            // Generate random password for SSO users since they use SSO auth
            $userData['password'] = Hash::make(Str::random(16));
        } else {
            $userData['password'] = Hash::make($request->password);
        }

        $user = $this->data::create($userData);

        $role = Role::findById($validated['role_id']);
        $user->assignRole($role);

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
            'roles' => Role::all(),
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
        ];

        if ($request->filled('password')) {
            $rules['password'] = ['confirmed', Rules\Password::defaults()];
        }

        $validated = $request->validate($rules);

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($request->filled('password')) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        $role = Role::findById($validated['role_id']);
        $user->syncRoles([$role]);

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
