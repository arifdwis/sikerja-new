<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        $now = now();

        // 1. Buat role tkksd_lokus jika belum ada
        $role = DB::table('roles')->where('slug', 'tkksd_lokus')->first();
        if (!$role) {
            $roleId = DB::table('roles')->insertGetId([
                'name'       => 'TKKSD Lokus Kerjasama',
                'slug'       => 'tkksd_lokus',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } else {
            $roleId = $role->id;
        }

        // 2. Permissions untuk tkksd_lokus
        // Schema permissions: id, name (UNIQUE), slug (UNIQUE), http_method, http_path
        $permissions = [
            ['name' => 'monev.index',  'slug' => 'monev-index'],
            ['name' => 'monev.show',   'slug' => 'monev-show'],
            ['name' => 'monev.create', 'slug' => 'monev-create'],
            ['name' => 'monev.review', 'slug' => 'monev-review'],
        ];

        foreach ($permissions as $perm) {
            $existing = DB::table('permissions')->where('name', $perm['name'])->first();
            if ($existing) {
                $permId = $existing->id;
            } else {
                $permId = DB::table('permissions')->insertGetId([
                    'name'       => $perm['name'],
                    'slug'       => $perm['slug'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // Assign ke role tkksd_lokus
            $linked = DB::table('role_permissions')
                ->where('role_id', $roleId)
                ->where('permission_id', $permId)
                ->exists();

            if (!$linked) {
                DB::table('role_permissions')->insert([
                    'role_id'       => $roleId,
                    'permission_id' => $permId,
                ]);
            }
        }
    }

    public function down(): void
    {
        $role = DB::table('roles')->where('slug', 'tkksd_lokus')->first();
        if ($role) {
            DB::table('role_permissions')->where('role_id', $role->id)->delete();
            DB::table('roles')->where('id', $role->id)->delete();
        }
    }
};
