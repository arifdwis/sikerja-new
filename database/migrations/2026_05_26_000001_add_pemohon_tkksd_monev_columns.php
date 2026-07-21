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
        Schema::table('monevs', function (Blueprint $table) {
            // Form khusus Pemohon
            $table->enum('pmh_realisasi_kegiatan', ['Terlaksana penuh', 'Sebagian', 'Tidak'])->nullable()->after('file_bukti');
            $table->enum('pmh_kesesuaian_output', ['Ya', 'Sebagian', 'Tidak'])->nullable()->after('pmh_realisasi_kegiatan');
            $table->text('pmh_pemanfaatan_hasil')->nullable()->after('pmh_kesesuaian_output');
            $table->text('pmh_kendala_lapangan')->nullable()->after('pmh_pemanfaatan_hasil');
            $table->enum('pmh_keberlanjutan', ['Perlu dilanjutkan', 'Cukup', 'Hentikan'])->nullable()->after('pmh_kendala_lapangan');
            $table->string('pmh_file_laporan')->nullable()->after('pmh_keberlanjutan');

            // Form khusus TKKSD Lokus
            $table->enum('tkl_kepatuhan_pks', ['Patuh', 'Sebagian', 'Tidak'])->nullable()->after('pmh_file_laporan');
            $table->enum('tkl_koordinasi_mitra', ['Sangat baik', 'Baik', 'Cukup', 'Kurang'])->nullable()->after('tkl_kepatuhan_pks');
            $table->enum('tkl_kesesuaian_anggaran', ['Sesuai', 'Sebagian', 'Tidak'])->nullable()->after('tkl_koordinasi_mitra');
            $table->text('tkl_temuan_pengawasan')->nullable()->after('tkl_kesesuaian_anggaran');
            $table->enum('tkl_rekomendasi_lokus', ['Lanjutkan', 'Perbaiki', 'Hentikan'])->nullable()->after('tkl_temuan_pengawasan');
            $table->text('tkl_catatan')->nullable()->after('tkl_rekomendasi_lokus');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monevs', function (Blueprint $table) {
            $table->dropColumn([
                'pmh_realisasi_kegiatan',
                'pmh_kesesuaian_output',
                'pmh_pemanfaatan_hasil',
                'pmh_kendala_lapangan',
                'pmh_keberlanjutan',
                'pmh_file_laporan',
                'tkl_kepatuhan_pks',
                'tkl_koordinasi_mitra',
                'tkl_kesesuaian_anggaran',
                'tkl_temuan_pengawasan',
                'tkl_rekomendasi_lokus',
                'tkl_catatan',
            ]);
        });
    }
};
