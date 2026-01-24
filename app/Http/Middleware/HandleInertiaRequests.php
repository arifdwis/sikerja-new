<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

use App\Models\Laman;

class HandleInertiaRequests extends Middleware
{
    // ...

    public function share(Request $request): array
    {
        $user = $request->user();

        // Fetch visible pages for the menu
        $lamanMenu = Laman::where('status', 1)
            ->select('label', 'slug')
            ->get()
            ->map(function ($page) {
                return [
                    'label' => $page->label,
                    'url' => route('landing.page', $page->slug) // Assuming this route exists/will use generic page controller
                ];
            });

        return [
            ...parent::share($request),
            'laman_menu' => $lamanMenu,
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'uid' => $user->uid,
                    'name' => $user->name,
                    'email' => $user->email,
                    'photo' => $user->photo,
                    'photo_url' => $user->photo_url,
                    'level' => $user->level,
                ] : null,
                'role' => $user ? $user->roles->first()?->slug : null,
                'roles' => $user ? $user->roles->pluck('slug')->toArray() : [],
                'permissions' => $user ? $this->getUserPermissions($user) : [],
                'menu' => $user ? $this->getUserMenu($user) : [],
            ],
            'flash' => [
                'success' => fn() => $request->session()->get('success'),
                'error' => fn() => $request->session()->get('error'),
                'warning' => fn() => $request->session()->get('warning'),
                'info' => fn() => $request->session()->get('info'),
            ],
            'ziggy' => fn() => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
        ];
    }

    /**
     * Get user permissions based on their roles
     */
    protected function getUserPermissions($user): array
    {
        $permissions = [];

        foreach ($user->roles as $role) {
            $rolePermissions = \DB::table('role_permissions')
                ->join('permissions', 'permissions.id', '=', 'role_permissions.permission_id')
                ->where('role_permissions.role_id', $role->id)
                ->pluck('permissions.name')
                ->toArray();

            $permissions = array_merge($permissions, $rolePermissions);
        }

        return array_unique($permissions);
    }

    protected function getUserMenu($user)
    {
        $roles = $user->roles->pluck('slug')->toArray();

        $menus = \App\Models\Menu::where('is_active', true)
            ->orderBy('order')
            ->get();

        return $this->buildMenuTree($menus, null, $roles);
    }

    protected function buildMenuTree($menus, $parentId, $userRoles)
    {
        $branch = [];
        foreach ($menus as $menu) {
            if ($menu->parent_id == $parentId) {
                if ($this->userHasAccessToMenu($menu, $userRoles)) {
                    $children = $this->buildMenuTree($menus, $menu->id, $userRoles);

                    $item = [
                        'id' => $menu->id,
                        'title' => $menu->title,
                        'route' => $menu->route,
                        'icon' => $menu->icon,
                        'children' => $children
                    ];

                    $branch[] = $item;
                }
            }
        }
        return $branch;
    }

    protected function userHasAccessToMenu($menu, $userRoles)
    {
        if (empty($menu->roles))
            return true;

        $allowedRoles = explode(',', $menu->roles);
        // Trim whitespace from roles just in case
        $allowedRoles = array_map('trim', $allowedRoles);

        return !empty(array_intersect($userRoles, $allowedRoles));
    }
}
