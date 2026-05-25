<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $now = now();

        $permissions = [
            ['name' => 'master.opd.index',   'slug' => 'master-opd-index'],
            ['name' => 'master.opd.create',  'slug' => 'master-opd-create'],
            ['name' => 'master.opd.edit',    'slug' => 'master-opd-edit'],
            ['name' => 'master.opd.destroy', 'slug' => 'master-opd-destroy'],
        ];

        // Assign ke admin & superadmin
        $adminRoles = DB::table('roles')->whereIn('slug', ['administrator', 'superadmin'])->pluck('id');

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

            foreach ($adminRoles as $roleId) {
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
    }

    public function down(): void
    {
        $names = ['master.opd.index', 'master.opd.create', 'master.opd.edit', 'master.opd.destroy'];
        $permIds = DB::table('permissions')->whereIn('name', $names)->pluck('id');
        DB::table('role_permissions')->whereIn('permission_id', $permIds)->delete();
        DB::table('permissions')->whereIn('id', $permIds)->delete();
    }
};
