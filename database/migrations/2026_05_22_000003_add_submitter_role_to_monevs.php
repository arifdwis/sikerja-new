<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Tambah kolom submitter_role + submitter_user_id ke tabel monevs.
 *
 * Workflow baru: pemohon, admin, dan TKKSD Lokus monev secara mandiri/paralel.
 * Setiap monev menyimpan siapa yang submit lewat submitter_role + submitter_user_id.
 * Admin bisa lihat semua; pemohon dan TKKSD Lokus hanya lihat monev miliknya.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('monevs', function (Blueprint $table) {
            if (!Schema::hasColumn('monevs', 'submitter_role')) {
                $table->string('submitter_role', 30)->nullable()->after('tipe')
                    ->comment('Role pengirim monev: pemohon | tkksd_lokus | admin');
            }
            if (!Schema::hasColumn('monevs', 'submitter_user_id')) {
                $table->foreignId('submitter_user_id')->nullable()->after('submitter_role')
                    ->constrained('users')->nullOnDelete();
            }
        });

        // Backfill existing data:
        //   - tipe='reguler' → submitter_role='pemohon'
        //   - tipe='manual' → submitter_role='admin'
        // submitter_user_id = id_pemohon's operator (best-effort) atau reviewed_by untuk manual.
        DB::statement("UPDATE monevs SET submitter_role='pemohon' WHERE tipe='reguler' AND submitter_role IS NULL");
        DB::statement("UPDATE monevs SET submitter_role='admin' WHERE tipe='manual' AND submitter_role IS NULL");
        DB::statement("UPDATE monevs m JOIN pemohon p ON p.id = m.id_pemohon SET m.submitter_user_id = p.id_operator WHERE m.submitter_role='pemohon' AND m.submitter_user_id IS NULL");
        DB::statement("UPDATE monevs SET submitter_user_id = reviewed_by WHERE submitter_role='admin' AND submitter_user_id IS NULL AND reviewed_by IS NOT NULL");
    }

    public function down(): void
    {
        Schema::table('monevs', function (Blueprint $table) {
            if (Schema::hasColumn('monevs', 'submitter_user_id')) {
                $table->dropForeign(['submitter_user_id']);
                $table->dropColumn('submitter_user_id');
            }
            if (Schema::hasColumn('monevs', 'submitter_role')) {
                $table->dropColumn('submitter_role');
            }
        });
    }
};
