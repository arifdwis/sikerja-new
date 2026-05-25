<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $now = now();

        // Cek apakah sudah ada menu top-level Monev
        $existing = DB::table('menus')
            ->whereNull('parent_id')
            ->where('route', 'monev.index')
            ->first();

        if ($existing) {
            // Update saja roles agar include pemohon dan tkksd_lokus
            DB::table('menus')->where('id', $existing->id)->update([
                'roles' => 'administrator,superadmin,tkksd,pemohon,tkksd_lokus',
                'is_active' => 1,
                'updated_at' => $now,
            ]);
            return;
        }

        // Tambah menu Monitoring & Evaluasi sebagai top-level
        // (tidak di bawah Laporan karena Laporan tidak include pemohon)
        $maxOrder = DB::table('menus')->whereNull('parent_id')->max('order') ?? 0;
        DB::table('menus')->insert([
            'parent_id' => null,
            'title' => 'Monitoring & Evaluasi',
            'route' => 'monev.index',
            'icon' => 'solar:clipboard-list-bold-duotone',
            'permission_name' => null,
            'is_active' => 1,
            'order' => $maxOrder + 1,
            'roles' => 'administrator,superadmin,tkksd,pemohon,tkksd_lokus',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        DB::table('menus')
            ->whereNull('parent_id')
            ->where('route', 'monev.index')
            ->where('title', 'Monitoring & Evaluasi')
            ->delete();
    }
};
