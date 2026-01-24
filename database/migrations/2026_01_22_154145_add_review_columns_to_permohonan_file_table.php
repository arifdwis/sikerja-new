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
        Schema::table('permohonan_file', function (Blueprint $table) {
            // Add review columns if they don't exist
            if (!Schema::hasColumn('permohonan_file', 'status')) {
                $table->tinyInteger('status')->default(0)->after('deskripsi'); // 0=pending, 1=approved, 2=rejected
            }
            if (!Schema::hasColumn('permohonan_file', 'komentar')) {
                $table->text('komentar')->nullable()->after('status');
            }
            if (!Schema::hasColumn('permohonan_file', 'reviewer_id')) {
                $table->foreignId('reviewer_id')->nullable()->after('komentar')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('permohonan_file', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('reviewer_id');
            }
            if (!Schema::hasColumn('permohonan_file', 'file_path')) {
                $table->string('file_path')->nullable()->after('file');
            }
            if (!Schema::hasColumn('permohonan_file', 'file_name')) {
                $table->string('file_name')->nullable()->after('file_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permohonan_file', function (Blueprint $table) {
            $table->dropColumn(['status', 'komentar', 'reviewer_id', 'reviewed_at', 'file_path', 'file_name']);
        });
    }
};
