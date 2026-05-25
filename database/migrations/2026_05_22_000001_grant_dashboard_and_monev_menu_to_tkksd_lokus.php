<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Grant izin yang hilang ke role tkksd_lokus.
 *
 * Tanpa permission ini:
 *  - 'dashboard'   → 403 saat akses /dashboard
 *  - 'monev.menu'  → 403 saat akses /monev (middleware can:monev.menu)
 *  - 'monev.view'  → 403 saat akses detail /monev/{uuid} (middleware can:monev.view)
 */
return new class extends Migration {
    public function up(): void
    {
        $role = DB::table('roles')->where('slug', 'tkksd_lokus')->first();
        if (!$role) {
            return; // role belum ada, migration sebelumnya yang harusnya bikin
        }

        $permNames = ['dashboard', 'monev.menu', 'monev.view'];
        $perms = DB::table('permissions')
            ->whereIn('name', $permNames)
            ->pluck('id', 'name');

        foreach ($perms as $name => $permId) {
            $linked = DB::table('role_permissions')
                ->where('role_id', $role->id)
                ->where('permission_id', $permId)
                ->exists();

            if (!$linked) {
                DB::table('role_permissions')->insert([
                    'role_id'       => $role->id,
                    'permission_id' => $permId,
                ]);
            }
        }
    }

    public function down(): void
    {
        $role = DB::table('roles')->where('slug', 'tkksd_lokus')->first();
        if (!$role) {
            return;
        }

        $permIds = DB::table('permissions')
            ->whereIn('name', ['dashboard', 'monev.menu', 'monev.view'])
            ->pluck('id');

        DB::table('role_permissions')
            ->where('role_id', $role->id)
            ->whereIn('permission_id', $permIds)
            ->delete();
    }
};
