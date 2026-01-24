<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use Illuminate\Support\Str;

class PermissionsSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            // Users
            'settings.users.index',
            'settings.users.create',
            'settings.users.edit',
            'settings.users.destroy',

            // Roles
            'settings.roles.index',
            'settings.roles.create',
            'settings.roles.edit',
            'settings.roles.destroy',
            'settings.roles.permission', // Manage permission of a role
            'settings.roles.permission.update',

            // Permissions
            'settings.permissions.index',
            'settings.permissions.create',
            'settings.permissions.edit',
            'settings.permissions.destroy',

            // Log Activity
            'settings.log-activity.index',
            'settings.log-activity.show',
            'settings.log-activity.destroy',

            // Menu
            'settings.menu.index',

            // Permohonan
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

            // Riwayat
            'riwayat.index',

            // Profile Pemohon
            'profile.edit',
            'profile.corporate',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['slug' => Str::slug($permission)],
                ['name' => $permission]
            );
        }
    }
}
