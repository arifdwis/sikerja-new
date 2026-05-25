<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Migrate existing data sebelum ubah kolom
        // calendar/langsung → desk_to_desk (default fallback)
        DB::table('penjadwalan')->whereIn('tipe', ['calendar', 'langsung', ''])->update(['tipe' => 'desk_to_desk']);
        DB::table('penjadwalan')->whereNull('tipe')->update(['tipe' => 'desk_to_desk']);

        // Ubah kolom tipe menjadi string dengan nilai baru
        // Tidak pakai enum agar lebih fleksibel ke depannya
        Schema::table('penjadwalan', function (Blueprint $table) {
            $table->string('tipe', 30)->default('desk_to_desk')->change()
                ->comment('desk_to_desk | seremonial | hybrid');
        });
    }

    public function down(): void
    {
        DB::table('penjadwalan')->whereIn('tipe', ['desk_to_desk', 'seremonial', 'hybrid'])->update(['tipe' => 'calendar']);

        Schema::table('penjadwalan', function (Blueprint $table) {
            $table->string('tipe', 20)->default('calendar')->change();
        });
    }
};
