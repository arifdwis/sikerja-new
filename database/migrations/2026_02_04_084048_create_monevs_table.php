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
        Schema::create('monevs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Relasi
            $table->foreignId('id_permohonan')->constrained('permohonans')->onDelete('cascade');
            $table->foreignId('id_operator')->constrained('operators')->onDelete('cascade');

            // Identitas
            $table->string('kode_monev')->unique();
            $table->date('tanggal_evaluasi');

            // Evaluasi Pelaksanaan
            $table->enum('kesesuaian_tujuan', ['Ya seluruhnya', 'Sebagian', 'Tidak'])->nullable();
            $table->enum('ketepatan_waktu', ['Tepat waktu', 'Terlambat', 'Tidak terlaksana'])->nullable();
            $table->enum('kontribusi_mitra', ['Ya sepenuhnya', 'Sebagian', 'Tidak'])->nullable();
            $table->enum('tingkat_koordinasi', ['Sangat baik', 'Baik', 'Cukup', 'Kurang'])->nullable();

            // Capaian & Dampak
            $table->enum('capaian_indikator', ['Tercapai seluruhnya', 'Sebagian', 'Tidak'])->nullable();
            $table->enum('dampak_pelaksanaan', ['Sangat berdampak', 'Cukup', 'Kurang'])->nullable();
            $table->enum('inovasi_manfaat', ['Ya signifikan', 'Ada', 'Tidak'])->nullable();

            // Administrasi
            $table->enum('kelengkapan_dokumen', ['Lengkap', 'Sebagian', 'Tidak'])->nullable();
            $table->enum('pelaporan_berkala', ['Rutin', 'Kadang', 'Tidak'])->nullable();
            $table->text('kendala_administrasi')->nullable();

            // Rekomendasi
            $table->enum('relevansi_kebutuhan', ['Sangat relevan', 'Cukup', 'Tidak'])->nullable();
            $table->enum('rekomendasi_lanjutan', ['Dilanjutkan', 'Diperluas', 'Dihentikan'])->nullable();
            $table->text('saran_rekomendasi')->nullable();

            // Bukti Pendukung
            $table->string('file_bukti')->nullable();

            // Status & Review
            $table->tinyInteger('status')->default(0); // 0=Draft, 1=Submitted, 2=Reviewed
            $table->text('catatan_admin')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monevs');
    }
};
