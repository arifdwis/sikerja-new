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
        // Clear existing role_permissions
        DB::table('role_permissions')->truncate();

        // Define permissions per role
        $rolePermissions = [
            // Superadmin - All permissions
            'superadmin' => ['*'], // All permission

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

            // TKKSD - Validasi & Pembahasan access
            'tkksd' => [
                'dashboard',
                'nue.profile',
                // Permohonan - Limited access
                'permohonan.index',
                'permohonan.show',
                'permohonan.status',
                'permohonan.menu.validasi',
                'permohonan.menu.pembahasan',
                'permohonan.menu.persetujuan', // Allow TKKSD to see persetujuan/finalize
                // Penjadwalan
                'penjadwalan.index',
                'penjadwalan.create',
                'penjadwalan.edit',
                // Riwayat
                'riwayat.index',
            ],

            // Pemohon - Create & view own permohonan
            'pemohon' => [
                'dashboard',
                'nue.profile',
                // Permohonan - Create & view
                'permohonan.index',
                'permohonan.create',
                'permohonan.show',
                'permohonan.edit', // Edit own only (controller handles ownership)
                // Profile & Corporate
                'profile.edit',
                'profile.corporate',
                // Riwayat
                'riwayat.index',
                // Monev
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
                // Handle wildcard permission
                if ($permName === '*') {
                    $permission = Permission::where('slug', '*')->first();
                } else {
                    $permission = Permission::where('name', $permName)->first();
                }

                if ($permission) {
                    // Check if already exists
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
