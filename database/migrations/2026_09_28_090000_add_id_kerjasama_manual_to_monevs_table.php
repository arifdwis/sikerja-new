<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Buat id_permohonan menjadi nullable agar monevs bisa menampung kerjasama manual
        DB::statement("ALTER TABLE monevs MODIFY id_permohonan BIGINT UNSIGNED NULL");

        // 2. Tambahkan id_kerjasama_manual
        Schema::table('monevs', function (Blueprint $table) {
            if (!Schema::hasColumn('monevs', 'id_kerjasama_manual')) {
                $table->foreignId('id_kerjasama_manual')
                    ->nullable()
                    ->after('id_permohonan')
                    ->constrained('kerjasama_manual')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monevs', function (Blueprint $table) {
            if (Schema::hasColumn('monevs', 'id_kerjasama_manual')) {
                $table->dropForeign(['id_kerjasama_manual']);
                $table->dropColumn('id_kerjasama_manual');
            }
        });

        DB::statement("ALTER TABLE monevs MODIFY id_permohonan BIGINT UNSIGNED NOT NULL");
    }
};
