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
        Schema::table('penjadwalan', function (Blueprint $table) {
            // Add scheduling type column
            if (!Schema::hasColumn('penjadwalan', 'tipe')) {
                $table->string('tipe', 20)->default('calendar')->after('id_histori'); // calendar or langsung
            }

            // Add approval columns
            if (!Schema::hasColumn('penjadwalan', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('penjadwalan', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
            if (!Schema::hasColumn('penjadwalan', 'admin_comment')) {
                $table->text('admin_comment')->nullable()->after('approved_at');
            }

            // Add created_by for pemohon
            if (!Schema::hasColumn('penjadwalan', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('keterangan')->constrained('users')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penjadwalan', function (Blueprint $table) {
            $table->dropColumn(['tipe', 'approved_by', 'approved_at', 'admin_comment', 'created_by']);
        });
    }
};
