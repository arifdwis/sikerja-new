<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('permohonan_pks')) {
            Schema::create('permohonan_pks', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                // permohonan.id adalah INT UNSIGNED (legacy schema)
                $table->unsignedInteger('id_permohonan');
                $table->string('file_path');
                $table->string('file_name');
                $table->string('tipe', 20)->default('pemohon')->comment('pemohon | admin');
                $table->string('status', 20)->default('pending')->comment('pending | approved | rejected');
                $table->foreignId('uploaded_by')->constrained('users');
                $table->text('catatan')->nullable();
                $table->timestamps();

                $table->foreign('id_permohonan')->references('id')->on('permohonan')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('permohonan_pks');
    }
};
