<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $now = now();

        // Pastikan permission monev.create ada
        $perm = DB::table('permissions')->where('name', 'monev.create')->first();
        if (!$perm) {
            $permId = DB::table('permissions')->insertGetId([
                'name' => 'monev.create',
                'slug' => 'monev-create',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } else {
            $permId = $perm->id;
        }

        // Assign ke role pemohon, tkksd_lokus, administrator, superadmin
        $roleSlugs = ['pemohon', 'tkksd_lokus', 'administrator', 'superadmin'];
        $roles = DB::table('roles')->whereIn('slug', $roleSlugs)->pluck('id', 'slug');

        foreach ($roles as $slug => $roleId) {
            $linked = DB::table('role_permissions')
                ->where('role_id', $roleId)
                ->where('permission_id', $permId)
                ->exists();

            if (!$linked) {
                DB::table('role_permissions')->insert([
                    'role_id' => $roleId,
                    'permission_id' => $permId,
                ]);
            }
        }
    }

    public function down(): void
    {
        $perm = DB::table('permissions')->where('name', 'monev.create')->first();
        if ($perm) {
            DB::table('role_permissions')->where('permission_id', $perm->id)->delete();
        }
    }
};
