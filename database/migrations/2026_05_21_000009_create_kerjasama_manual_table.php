<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('kerjasama_manual')) {
            Schema::create('kerjasama_manual', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('nomor_pks')->nullable();
                $table->string('nama_instansi');
                $table->string('label'); // perihal kerjasama
                // kategori.id adalah INT UNSIGNED (legacy schema)
                $table->unsignedInteger('id_kategori')->nullable();
                $table->text('ruang_lingkup')->nullable();
                $table->date('tanggal_mulai')->nullable();
                $table->date('tanggal_berakhir')->nullable();
                $table->string('jangka_waktu')->nullable();
                $table->string('file_pks')->nullable();
                $table->string('file_pks_name')->nullable();
                $table->foreignId('created_by')->constrained('users');
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('id_kategori')->references('id')->on('kategori')->nullOnDelete();
            });
        }

        if (!Schema::hasTable('kerjasama_manual_opd')) {
            Schema::create('kerjasama_manual_opd', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_kerjasama_manual');
                $table->unsignedBigInteger('id_opd');
                $table->timestamps();

                $table->foreign('id_kerjasama_manual')->references('id')->on('kerjasama_manual')->cascadeOnDelete();
                $table->foreign('id_opd')->references('id')->on('opd')->cascadeOnDelete();
                $table->unique(['id_kerjasama_manual', 'id_opd']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('kerjasama_manual_opd');
        Schema::dropIfExists('kerjasama_manual');
    }
};
