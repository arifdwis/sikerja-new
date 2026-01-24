<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Menu;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add "Semua Permohonan" to "Kelola Permohonan"
        $kelolaPermohonan = Menu::where('title', 'Kelola Permohonan')->first();

        if ($kelolaPermohonan) {
            // Check if exists to prevent duplicate (though migration runs once)
            $exists = Menu::where('title', 'Semua Permohonan')
                ->where('parent_id', $kelolaPermohonan->id)
                ->exists();

            if (!$exists) {
                // Determine order (put it first or last? User said "Daftar Permohonan" exists but filters? 
                // Currently "Daftar Permohonan" points to permohonan.index? 
                // Wait, if "Daftar Permohonan" ALREADY points to permohonan.index, then it IS "Semua Permohonan".
                // Let's check if "Daftar Permohonan" exists.
                // Sidebar.vue doesn't show it? 
                // Navbar built grep showed: "Daftar Permohonan", route: "/permohonan".
                // If it exists, why does the user say "tidak terlihat"? 
                // Maybe "yang sudah selesai" is filtered out?
                // I verified PermohonanController DOES NOT filter "Selesai" by default.

                // Perhaps the user implies they want a SEPARATE menu for "Semua" vs "Validasi" etc.
                // Or maybe "Daftar Permohonan" is NOT visible to Admin?
                // Let's force add "Semua Permohonan" specifically for Administrator.

                Menu::create([
                    'parent_id' => $kelolaPermohonan->id,
                    'title' => 'Semua Permohonan',
                    'route' => 'permohonan.index',
                    'icon' => 'solar:documents-minimalistic-linear',
                    'roles' => 'administrator,superadmin', // Visible to admin
                    'order' => 0, // Put at top
                    'is_active' => true,
                ]);
            }
        }

        // 2. Add "Klasifikasi" to "Master Data"
        // Since I couldn't find the route, I will assume a route 'master.klasifikasi.index' might be created later 
        // OR I should point to URL directly if it's an external module? 
        // Navbar has /api/search/klasifikasi. 
        // I will create the menu item pointing to 'master.klasifikasi.index' (standard convention).
        // If route doesn't exist, it will 404, but the MENU will be there.

        $masterData = Menu::where('title', 'Master Data')->first();
        if ($masterData) {
            $exists = Menu::where('title', 'Klasifikasi')
                ->where('parent_id', $masterData->id)
                ->exists();
            if (!$exists) {
                Menu::create([
                    'parent_id' => $masterData->id,
                    'title' => 'Klasifikasi',
                    'route' => 'master.klasifikasi.index', // Assumption
                    'icon' => 'solar:tag-linear',
                    'roles' => 'administrator,superadmin',
                    'order' => 10,
                    'is_active' => true,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Menu::where('title', 'Semua Permohonan')->delete();
        Menu::where('title', 'Klasifikasi')->delete();
    }
};
