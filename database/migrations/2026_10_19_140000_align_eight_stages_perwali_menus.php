<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menyelaraskan Struktur Menu Sidebar dengan 8 Tahapan Alur Kerja Sama
     * Peraturan Walikota Samarinda Nomor 7 Tahun 2020 dan Dokumen Proses Bisnis SIKERJA.
     */
    public function up(): void
    {
        // 1. Parent "Alur Kerja Sama" (ID: 4)
        DB::table('menus')->where('id', 4)->update([
            'title' => 'Alur Kerja Sama',
            'order' => 2,
        ]);

        // Tahap 1: Pengajuan Usulan Permohonan
        DB::table('menus')->where('id', 20)->update([
            'parent_id' => 4,
            'title' => '1. Pengajuan Usulan',
            'route' => 'permohonan.index',
            'icon' => 'solar:document-add-linear',
            'order' => 1,
            'roles' => 'administrator,superadmin,tkksd,kabag_kerjasama',
        ]);

        // Tahap 2: Verifikasi & Telaah Administrasi
        DB::table('menus')->where('id', 6)->update([
            'parent_id' => 4,
            'title' => '2. Verifikasi & Telaah',
            'route' => 'validasi.index',
            'icon' => 'solar:checklist-minimalistic-linear',
            'order' => 2,
            'roles' => 'administrator,superadmin,tkksd,kabag_kerjasama',
        ]);

        // Tahap 3: Pembahasan Draf KB
        DB::table('menus')->where('id', 7)->update([
            'parent_id' => 4,
            'title' => '3. Pembahasan Draf KB',
            'route' => '/pembahasan?type=kb',
            'icon' => 'solar:chat-round-dots-linear',
            'order' => 3,
            'roles' => 'administrator,superadmin,tkksd,kabag_kerjasama',
        ]);

        // Tahap 4: Penjadwalan & Penandatanganan KB
        DB::table('menus')->where('id', 8)->update([
            'parent_id' => 4,
            'title' => '4. Penandatanganan KB',
            'route' => 'persetujuan.index',
            'icon' => 'solar:verified-check-linear',
            'order' => 4,
            'roles' => 'administrator,superadmin,tkksd,kabag_kerjasama',
        ]);

        // Tahap 5: Pembahasan Draf PKS Turunan (Insert if not exists or update)
        $pksMenu = DB::table('menus')->where('id', 59)->first();
        if (!$pksMenu) {
            DB::table('menus')->insert([
                'id' => 59,
                'parent_id' => 4,
                'title' => '5. Pembahasan Draf PKS',
                'route' => '/pembahasan?type=pks',
                'icon' => 'solar:documents-minimalistic-linear',
                'permission_name' => null,
                'is_active' => 1,
                'order' => 5,
                'roles' => 'administrator,superadmin,tkksd,kabag_kerjasama',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('menus')->where('id', 59)->update([
                'parent_id' => 4,
                'title' => '5. Pembahasan Draf PKS',
                'route' => '/pembahasan?type=pks',
                'icon' => 'solar:documents-minimalistic-linear',
                'order' => 5,
                'roles' => 'administrator,superadmin,tkksd,kabag_kerjasama',
            ]);
        }

        // Tahap 6: Penandatanganan PKS
        DB::table('menus')->where('id', 57)->update([
            'parent_id' => 4,
            'title' => '6. Penandatanganan PKS',
            'route' => 'penandatanganan.index',
            'icon' => 'solar:pen-new-square-linear',
            'order' => 6,
            'roles' => 'administrator,superadmin,tkksd,kabag_kerjasama',
        ]);

        // Tahap 7: Pelaksanaan Kerja Sama
        DB::table('menus')->where('id', 58)->update([
            'parent_id' => 4,
            'title' => '7. Pelaksanaan Kerja Sama',
            'route' => 'pelaksanaan.index',
            'icon' => 'solar:play-circle-linear',
            'order' => 7,
            'roles' => 'administrator,superadmin,tkksd,kabag_kerjasama',
        ]);

        // Tahap 8: Pengendalian, Evaluasi & Monev
        DB::table('menus')->where('id', 52)->update([
            'parent_id' => 4,
            'title' => '8. Evaluasi & Monev',
            'route' => 'monev.index',
            'icon' => 'solar:clipboard-check-linear',
            'order' => 8,
            'roles' => 'administrator,superadmin,tkksd,pemohon,tkksd_lokus,kabag_kerjasama',
        ]);

        // Database Rekapitulasi / Permohonan Selesai
        DB::table('menus')->where('id', 24)->update([
            'parent_id' => 4,
            'title' => 'Database Rekapitulasi',
            'route' => 'permohonan.selesai',
            'icon' => 'solar:archive-check-linear',
            'order' => 9,
            'roles' => 'administrator,superadmin,tkksd,kabag_kerjasama',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('menus')->where('id', 20)->update([
            'title' => 'Daftar Permohonan',
            'order' => 1,
            'roles' => 'administrator,superadmin,kabag_kerjasama',
        ]);

        DB::table('menus')->where('id', 6)->update(['title' => '1. Verifikasi & Telaah', 'order' => 2]);
        DB::table('menus')->where('id', 7)->update(['title' => '2. Pembahasan TKKSD', 'route' => 'pembahasan.index', 'order' => 3]);
        DB::table('menus')->where('id', 8)->update(['title' => '3. Penjadwalan & Persetujuan', 'order' => 4]);
        DB::table('menus')->where('id', 59)->delete();
        DB::table('menus')->where('id', 57)->update(['title' => '4. Penandatanganan', 'order' => 5]);
        DB::table('menus')->where('id', 58)->update(['title' => '5. Pelaksanaan Kerja Sama', 'order' => 6]);
        DB::table('menus')->where('id', 52)->update(['parent_id' => 10, 'title' => 'Monev Kerjasama', 'order' => 3]);
        DB::table('menus')->where('id', 24)->update(['title' => '6. Kerja Sama Selesai', 'order' => 7]);
    }
};
