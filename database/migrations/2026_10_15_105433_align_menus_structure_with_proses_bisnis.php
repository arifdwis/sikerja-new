<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Berikan role kabag_kerjasama ke menu-menu operasional & pelaporan
        $menuIdsForKabag = [1, 4, 6, 7, 8, 10, 20, 24, 48, 52, 53, 56, 57, 58];

        foreach ($menuIdsForKabag as $menuId) {
            $menu = DB::table('menus')->where('id', $menuId)->first();
            if ($menu && !empty($menu->roles)) {
                $roles = array_map('trim', explode(',', $menu->roles));
                if (!in_array('kabag_kerjasama', $roles)) {
                    $roles[] = 'kabag_kerjasama';
                    DB::table('menus')->where('id', $menuId)->update([
                        'roles' => implode(',', array_unique($roles)),
                    ]);
                }
            }
        }

        // 2. Perbarui label menu di bawah "Kelola Permohonan" (parent_id = 4) agar runut dan informatif
        DB::table('menus')->where('id', 4)->update([
            'title' => 'Alur Kerja Sama',
            'order' => 2,
        ]);

        DB::table('menus')->where('id', 20)->update([
            'title' => 'Daftar Permohonan',
            'order' => 1,
        ]);

        DB::table('menus')->where('id', 6)->update([
            'title' => '1. Verifikasi & Telaah',
            'order' => 2,
        ]);

        DB::table('menus')->where('id', 7)->update([
            'title' => '2. Pembahasan TKKSD',
            'order' => 3,
        ]);

        DB::table('menus')->where('id', 8)->update([
            'title' => '3. Penjadwalan & Persetujuan',
            'order' => 4,
        ]);

        DB::table('menus')->where('id', 57)->update([
            'title' => '4. Penandatanganan',
            'order' => 5,
        ]);

        DB::table('menus')->where('id', 58)->update([
            'title' => '5. Pelaksanaan Kerja Sama',
            'order' => 6,
        ]);

        DB::table('menus')->where('id', 24)->update([
            'title' => '6. Kerja Sama Selesai',
            'order' => 7,
            'roles' => 'administrator,superadmin,tkksd,kabag_kerjasama',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert titles
        DB::table('menus')->where('id', 4)->update(['title' => 'Kelola Permohonan']);
        DB::table('menus')->where('id', 20)->update(['title' => 'Semua Permohonan']);
        DB::table('menus')->where('id', 6)->update(['title' => 'Validasi']);
        DB::table('menus')->where('id', 7)->update(['title' => 'Pembahasan']);
        DB::table('menus')->where('id', 8)->update(['title' => 'Persetujuan']);
        DB::table('menus')->where('id', 57)->update(['title' => 'Penandatanganan']);
        DB::table('menus')->where('id', 58)->update(['title' => 'Pelaksanaan']);
        DB::table('menus')->where('id', 24)->update(['title' => 'Permohonan Selesai']);
    }
};
