<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Grant permohonan.show ke role tkksd_lokus supaya mereka bisa melihat detail
 * kerjasama OPD-nya (read-only). Tanpa ini, klik tombol mata di /tkksd-lokus/kerjasama
 * akan menghasilkan 403.
 */
return new class extends Migration {
    public function up(): void
    {
        $role = DB::table('roles')->where('slug', 'tkksd_lokus')->first();
        if (!$role) return;

        $perm = DB::table('permissions')->where('name', 'permohonan.show')->first();
        if (!$perm) return;

        $exists = DB::table('role_permissions')
            ->where('role_id', $role->id)
            ->where('permission_id', $perm->id)
            ->exists();

        if (!$exists) {
            DB::table('role_permissions')->insert([
                'role_id'       => $role->id,
                'permission_id' => $perm->id,
            ]);
        }

        // permohonan.index juga dibutuhkan karena show() redirect ke route index dengan ?detail
        $permIndex = DB::table('permissions')->where('name', 'permohonan.index')->first();
        if ($permIndex) {
            $exists = DB::table('role_permissions')
                ->where('role_id', $role->id)
                ->where('permission_id', $permIndex->id)
                ->exists();
            if (!$exists) {
                DB::table('role_permissions')->insert([
                    'role_id'       => $role->id,
                    'permission_id' => $permIndex->id,
                ]);
            }
        }
    }

    public function down(): void
    {
        $role = DB::table('roles')->where('slug', 'tkksd_lokus')->first();
        if (!$role) return;

        $permIds = DB::table('permissions')
            ->whereIn('name', ['permohonan.show', 'permohonan.index'])
            ->pluck('id');

        DB::table('role_permissions')
            ->where('role_id', $role->id)
            ->whereIn('permission_id', $permIds)
            ->delete();
    }
};
