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
        // Add Slider Menu
        $masterData = Menu::where('title', 'Master Data')->first();
        if ($masterData) {
            Menu::firstOrCreate([
                'title' => 'Slider',
                'route' => 'master.slider.index',
                'parent_id' => $masterData->id,
            ], [
                'icon' => 'solar:slider-minimalistic-horizontal-broken', // Example icon
                'order' => 4,
                'is_active' => true,
            ]);

            Menu::firstOrCreate([
                'title' => 'FAQ',
                'route' => 'master.faq.index',
                'parent_id' => $masterData->id,
            ], [
                'icon' => 'solar:question-circle-broken', // Example icon
                'order' => 5,
                'is_active' => true,
            ]);
        }

        // Add Permohonan Selesai Menu
        $kelolaPermohonan = Menu::where('title', 'Kelola Permohonan')->first();
        if ($kelolaPermohonan) {
            Menu::firstOrCreate([
                'title' => 'Permohonan Selesai',
                'route' => 'permohonan.selesai',
                'parent_id' => $kelolaPermohonan->id,
            ], [
                'icon' => 'solar:check-circle-broken',
                'order' => 5, // After other submenus
                'is_active' => true,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Menu::where('route', 'master.slider.index')->delete();
        Menu::where('route', 'master.faq.index')->delete();
        Menu::where('route', 'permohonan.selesai')->delete();
    }
};
