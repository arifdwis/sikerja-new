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
        Schema::create('penjadwalan', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedInteger('id_permohonan');
            $table->foreign('id_permohonan')->references('id')->on('permohonan')->onDelete('cascade');
            $table->integer('id_histori')->nullable()->index(); // Asumsi link ke tabel histori lama/baru
            $table->date('tanggal');
            $table->time('waktu');
            $table->string('lokasi');
            $table->string('agenda');
            $table->text('keterangan')->nullable();
            $table->integer('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjadwalan');
    }
};
