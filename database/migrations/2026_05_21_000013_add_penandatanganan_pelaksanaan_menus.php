<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $now = now();

        // Cari parent "Kelola Permohonan"
        $parent = DB::table('menus')
            ->whereIn('title', ['Kelola Permohonan', 'Permohonan'])
            ->first();

        if (!$parent) {
            $this->command?->warn('Parent menu Kelola Permohonan tidak ditemukan, skip seeding sub-menus.');
            return;
        }

        $newMenus = [
            [
                'title' => 'Penandatanganan',
                'route' => 'penandatanganan.index',
                'icon' => 'solar:pen-new-square-bold-duotone',
                'order' => 5,
                'roles' => 'administrator,superadmin,tkksd',
            ],
            [
                'title' => 'Pelaksanaan',
                'route' => 'pelaksanaan.index',
                'icon' => 'solar:running-2-bold-duotone',
                'order' => 6,
                'roles' => 'administrator,superadmin,tkksd',
            ],
        ];

        foreach ($newMenus as $menu) {
            $exists = DB::table('menus')
                ->where('route', $menu['route'])
                ->exists();

            if (!$exists) {
                DB::table('menus')->insert([
                    'parent_id' => $parent->id,
                    'title' => $menu['title'],
                    'route' => $menu['route'],
                    'icon' => $menu['icon'],
                    'permission_name' => null,
                    'is_active' => 1,
                    'order' => $menu['order'],
                    'roles' => $menu['roles'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // Geser order "Permohonan Selesai" agar di paling bawah
        DB::table('menus')
            ->where('route', 'permohonan.selesai')
            ->update(['order' => 7, 'updated_at' => $now]);
    }

    public function down(): void
    {
        DB::table('menus')->whereIn('route', [
            'penandatanganan.index',
            'pelaksanaan.index',
        ])->delete();
    }
};
