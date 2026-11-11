<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

class RolePermissionsSeeder extends Seeder
{
    public function run()
    {
        DB::table('role_permissions')->truncate();

        $rolePermissions = [
            // Superadmin - All permissions
            'superadmin' => ['*'],

            // Administrator - Full access to settings and permohonan management
            'administrator' => [
                'dashboard',
                'nue.profile',
                'nue.settings',
                // Settings - Users
                'settings.users.index',
                'settings.users.create',
                'settings.users.edit',
                'settings.users.destroy',
                // Settings - Roles
                'settings.roles.index',
                'settings.roles.create',
                'settings.roles.edit',
                'settings.roles.destroy',
                'settings.roles.permission',
                'settings.roles.permission.update',
                // Settings - Permissions
                'settings.permissions.index',
                'settings.permissions.create',
                'settings.permissions.edit',
                'settings.permissions.destroy',
                // Settings - Log Activity
                'settings.log-activity.index',
                'settings.log-activity.show',
                'settings.log-activity.destroy',
                // Settings - Menu
                'settings.menu.index',
                // Permohonan - Full access
                'permohonan.index',
                'permohonan.create',
                'permohonan.edit',
                'permohonan.show',
                'permohonan.destroy',
                'permohonan.status',
                'permohonan.menu.validasi',
                'permohonan.menu.pembahasan',
                'permohonan.menu.persetujuan',
                // Master Data - Kategori
                'master.kategori.index',
                'master.kategori.create',
                'master.kategori.edit',
                'master.kategori.destroy',
                // Master Data - Pemohon
                'master.pemohon.index',
                'master.pemohon.create',
                'master.pemohon.edit',
                'master.pemohon.destroy',
                // Penjadwalan
                'penjadwalan.index',
                'penjadwalan.create',
                'penjadwalan.edit',
                'penjadwalan.destroy',
                // Riwayat
                'riwayat.index',
                // Monev
                'monev.menu',
                'monev.menu.admin',
                'monev.create',
                'monev.view',
                'monev.review',
            ],

            // Kepala Bagian Kerja Sama (Kabag)
            'kabag_kerjasama' => [
                'dashboard',
                'nue.profile',
                'permohonan.index',
                'permohonan.show',
                'permohonan.status',
                'permohonan.menu.validasi',
                'permohonan.menu.pembahasan',
                'permohonan.menu.persetujuan',
                'penjadwalan.index',
                'penjadwalan.create',
                'penjadwalan.edit',
                'riwayat.index',
                'monev.menu',
                'monev.menu.admin',
                'monev.view',
                'monev.review',
            ],

            // TKKSD - Validasi & Pembahasan access
            'tkksd' => [
                'dashboard',
                'nue.profile',
                'permohonan.index',
                'permohonan.show',
                'permohonan.status',
                'permohonan.menu.validasi',
                'permohonan.menu.pembahasan',
                'permohonan.menu.persetujuan',
                'penjadwalan.index',
                'penjadwalan.create',
                'penjadwalan.edit',
                'riwayat.index',
            ],

            // TKKSD Lokus OPD - Monev & Kerjasama OPD
            'tkksd_lokus' => [
                'dashboard',
                'nue.profile',
                'permohonan.show',
                'monev.menu',
                'monev.create',
                'monev.view',
            ],

            // Pemohon - Create & view own permohonan + submit monev
            'pemohon' => [
                'dashboard',
                'nue.profile',
                'permohonan.index',
                'permohonan.create',
                'permohonan.show',
                'permohonan.edit',
                'profile.edit',
                'profile.corporate',
                'riwayat.index',
                'monev.menu',
                'monev.create',
                'monev.view',
            ],
        ];

        foreach ($rolePermissions as $roleSlug => $permissionNames) {
            $role = Role::where('slug', $roleSlug)->first();
            if (!$role) {
                $this->command->warn("Role '{$roleSlug}' not found, skipping...");
                continue;
            }

            foreach ($permissionNames as $permName) {
                if ($permName === '*') {
                    $permission = Permission::where('slug', '*')->first();
                } else {
                    $permission = Permission::where('name', $permName)->first();
                }

                if ($permission) {
                    $exists = DB::table('role_permissions')
                        ->where('role_id', $role->id)
                        ->where('permission_id', $permission->id)
                        ->exists();

                    if (!$exists) {
                        DB::table('role_permissions')->insert([
                            'role_id' => $role->id,
                            'permission_id' => $permission->id,
                        ]);
                    }
                } else {
                    $this->command->warn("Permission '{$permName}' not found for role '{$roleSlug}'");
                }
            }

            $this->command->info("Permissions assigned to role: {$roleSlug}");
        }

        $this->command->info('Role permissions seeding completed!');
    }
}
