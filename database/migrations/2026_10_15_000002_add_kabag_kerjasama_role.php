<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Cek atau buat role Kepala Bagian Kerja Sama
        $role = DB::table('roles')->where('slug', 'kabag_kerjasama')->first();
        if (!$role) {
            $roleId = DB::table('roles')->insertGetId([
                'name' => 'Kepala Bagian Kerja Sama',
                'slug' => 'kabag_kerjasama',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $roleId = $role->id;
        }

        // 2. Beri permissions ke role kabag_kerjasama (salin permission dari role Administrator)
        $adminPermissions = DB::table('role_permissions')->where('role_id', 1)->pluck('permission_id');
        foreach ($adminPermissions as $permId) {
            $exists = DB::table('role_permissions')
                ->where('role_id', $roleId)
                ->where('permission_id', $permId)
                ->exists();
            if (!$exists) {
                DB::table('role_permissions')->insert([
                    'role_id' => $roleId,
                    'permission_id' => $permId,
                ]);
            }
        }

        // 3. Tambah permission spesifik bila belum ada
        $kabagPerms = [
            'permohonan.validasi.kabag' => 'Validasi dan Disposisi Kepala Bagian',
            'permohonan.verifikasi.admin' => 'Pemeriksaan Kelengkapan Berkas Staff Admin',
        ];

        foreach ($kabagPerms as $permName => $permDesc) {
            $perm = DB::table('permissions')->where('name', $permName)->first();
            if (!$perm) {
                $permId = DB::table('permissions')->insertGetId([
                    'name' => $permName,
                    'slug' => Str::slug($permName),
                    'http_method' => '',
                    'http_path' => '',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $permId = $perm->id;
            }

            // Assign ke Kabag & Administrator
            foreach ([$roleId, 1, 2] as $rId) {
                $has = DB::table('role_permissions')
                    ->where('role_id', $rId)
                    ->where('permission_id', $permId)
                    ->exists();
                if (!$has) {
                    DB::table('role_permissions')->insert([
                        'role_id' => $rId,
                        'permission_id' => $permId,
                    ]);
                }
            }
        }

        // 4. Assign role kabag_kerjasama ke User ID 13 (Idfi Septiani, NIP: 198609122004122001)
        $user13 = DB::table('users')->where('id', 13)->first();
        if ($user13) {
            $hasRole = DB::table('role_users')
                ->where('user_id', 13)
                ->where('role_id', $roleId)
                ->exists();
            if (!$hasRole) {
                DB::table('role_users')->insert([
                    'role_id' => $roleId,
                    'user_id' => 13,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $role = DB::table('roles')->where('slug', 'kabag_kerjasama')->first();
        if ($role) {
            DB::table('role_users')->where('role_id', $role->id)->delete();
            DB::table('role_permissions')->where('role_id', $role->id)->delete();
            DB::table('roles')->where('id', $role->id)->delete();
        }
    }
};
