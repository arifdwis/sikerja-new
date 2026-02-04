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
        // Just rename the column - no foreign key needed
        if (Schema::hasColumn('monevs', 'id_operator')) {
            Schema::table('monevs', function (Blueprint $table) {
                $table->renameColumn('id_operator', 'id_pemohon');
            });

            // Make it nullable
            Schema::table('monevs', function (Blueprint $table) {
                $table->unsignedBigInteger('id_pemohon')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('monevs', 'id_pemohon')) {
            Schema::table('monevs', function (Blueprint $table) {
                $table->renameColumn('id_pemohon', 'id_operator');
            });
        }
    }
};
