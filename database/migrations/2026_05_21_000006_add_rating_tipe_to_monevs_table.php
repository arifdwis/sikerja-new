<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('monevs', function (Blueprint $table) {
            if (!Schema::hasColumn('monevs', 'rating')) {
                $table->tinyInteger('rating')->nullable()->after('saran_rekomendasi')
                    ->comment('Rating 1-5 bintang untuk monev final');
            }
            if (!Schema::hasColumn('monevs', 'tipe')) {
                $table->string('tipe', 20)->default('reguler')->after('rating')
                    ->comment('reguler = dari permohonan online | manual = diinput admin');
            }
            if (!Schema::hasColumn('monevs', 'id_tkksd_lokus')) {
                $table->foreignId('id_tkksd_lokus')->nullable()->after('tipe')
                    ->constrained('users')->nullOnDelete()
                    ->comment('User TKKSD Lokus yang mereview evaluasi pemohon');
            }
            if (!Schema::hasColumn('monevs', 'tkksd_approved_at')) {
                $table->timestamp('tkksd_approved_at')->nullable()->after('id_tkksd_lokus');
            }
            if (!Schema::hasColumn('monevs', 'tkksd_catatan')) {
                $table->text('tkksd_catatan')->nullable()->after('tkksd_approved_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('monevs', function (Blueprint $table) {
            $table->dropForeign(['id_tkksd_lokus']);
            $table->dropColumn(['rating', 'tipe', 'id_tkksd_lokus', 'tkksd_approved_at', 'tkksd_catatan']);
        });
    }
};
