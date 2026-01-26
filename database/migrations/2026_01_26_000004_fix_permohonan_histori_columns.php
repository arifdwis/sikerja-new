<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('permohonan_histori')) {
            Schema::table('permohonan_histori', function (Blueprint $table) {
                if (!Schema::hasColumn('permohonan_histori', 'id_file')) {
                    $table->foreignId('id_file')->nullable()->after('id_operator')->constrained('permohonan_file')->nullOnDelete();
                }
                if (!Schema::hasColumn('permohonan_histori', 'komentar')) {
                    $table->text('komentar')->nullable()->after('deskripsi');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('permohonan_histori')) {
            Schema::table('permohonan_histori', function (Blueprint $table) {
                if (Schema::hasColumn('permohonan_histori', 'id_file')) {
                    $table->dropForeign(['id_file']);
                    $table->dropColumn('id_file');
                }
                if (Schema::hasColumn('permohonan_histori', 'komentar')) {
                    $table->dropColumn('komentar');
                }
            });
        }
    }
};
