<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $now = now();

        // Cari parent "Master Data" untuk submenu OPD
        $masterParent = DB::table('menus')->where('title', 'Master Data')->first();

        if ($masterParent) {
            // Tambah menu Master OPD di bawah Master Data (jika belum ada)
            $exists = DB::table('menus')
                ->where('parent_id', $masterParent->id)
                ->where('route', 'master.opd.index')
                ->exists();

            if (!$exists) {
                DB::table('menus')->insert([
                    'parent_id' => $masterParent->id,
                    'title' => 'OPD',
                    'route' => 'master.opd.index',
                    'icon' => 'solar:buildings-2-bold-duotone',
                    'permission_name' => 'master.opd.index',
                    'is_active' => 1,
                    'order' => 2,
                    'roles' => 'administrator,superadmin',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // Tambah menu Kerjasama Manual (top-level, admin only)
        $kmExists = DB::table('menus')->where('route', 'kerjasama-manual.index')->exists();
        if (!$kmExists) {
            // Cari order tertinggi untuk diletakkan setelah menu permohonan
            $maxOrder = DB::table('menus')->whereNull('parent_id')->max('order') ?? 0;
            DB::table('menus')->insert([
                'parent_id' => null,
                'title' => 'Kerjasama Manual',
                'route' => 'kerjasama-manual.index',
                'icon' => 'solar:hand-shake-bold-duotone',
                'permission_name' => null,
                'is_active' => 1,
                'order' => $maxOrder + 1,
                'roles' => 'administrator,superadmin',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('menus')->where('route', 'master.opd.index')->delete();
        DB::table('menus')->where('route', 'kerjasama-manual.index')->delete();
    }
};
