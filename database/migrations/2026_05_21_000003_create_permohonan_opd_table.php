<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('permohonan_opd')) {
            Schema::create('permohonan_opd', function (Blueprint $table) {
                $table->id();
                // permohonan.id adalah INT UNSIGNED (legacy schema)
                $table->unsignedInteger('id_permohonan');
                // opd.id adalah BIGINT UNSIGNED (Laravel default)
                $table->unsignedBigInteger('id_opd');
                $table->timestamps();

                $table->foreign('id_permohonan')->references('id')->on('permohonan')->cascadeOnDelete();
                $table->foreign('id_opd')->references('id')->on('opd')->cascadeOnDelete();
                $table->unique(['id_permohonan', 'id_opd']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('permohonan_opd');
    }
};
