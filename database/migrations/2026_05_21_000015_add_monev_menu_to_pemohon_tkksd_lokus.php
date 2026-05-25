<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Tambahkan pemohon & tkksd_lokus ke menu Monev Kerjasama
        // (Req 11: pemohon dan TKKSD Lokus harus bisa akses monev)
        $monevMenu = DB::table('menus')->where('route', 'monev.index')->first();
        if ($monevMenu) {
            $existingRoles = array_filter(array_map('trim', explode(',', $monevMenu->roles ?: '')));
            $newRoles = array_unique(array_merge($existingRoles, ['pemohon', 'tkksd_lokus']));

            DB::table('menus')->where('id', $monevMenu->id)->update([
                'roles' => implode(',', $newRoles),
                'updated_at' => now(),
            ]);
        }

        // Riwayat menu juga harus bisa diakses pemohon
        $riwayatMenu = DB::table('menus')->where('route', 'riwayat.index')->first();
        if ($riwayatMenu) {
            $existingRoles = array_filter(array_map('trim', explode(',', $riwayatMenu->roles ?: '')));
            if (!in_array('pemohon', $existingRoles)) {
                $existingRoles[] = 'pemohon';
                DB::table('menus')->where('id', $riwayatMenu->id)->update([
                    'roles' => implode(',', $existingRoles),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Tidak perlu rollback role mapping (low risk)
    }
};
