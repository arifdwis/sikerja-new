<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('permohonan_ttd')) {
            Schema::create('permohonan_ttd', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                // permohonan.id adalah INT UNSIGNED (legacy schema)
                $table->unsignedInteger('id_permohonan');
                $table->string('file_path');
                $table->string('file_name');
                $table->string('tipe', 20)->default('pemohon')->comment('pemohon | admin');
                // Checklist kelengkapan dokumen
                $table->boolean('checklist_paraf')->default(false);
                $table->boolean('checklist_materai')->default(false);
                $table->boolean('checklist_stempel')->default(false);
                // Validasi admin
                $table->boolean('is_validated')->default(false);
                $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('validated_at')->nullable();
                $table->text('catatan_admin')->nullable();
                $table->foreignId('uploaded_by')->constrained('users');
                $table->timestamps();

                $table->foreign('id_permohonan')->references('id')->on('permohonan')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('permohonan_ttd');
    }
};
