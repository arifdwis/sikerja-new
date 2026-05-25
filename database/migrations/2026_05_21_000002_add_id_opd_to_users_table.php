<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'id_opd')) {
                // FK ke tabel opd — nullable karena hanya user internal Samarinda (SSO) yang punya OPD
                $table->foreignId('id_opd')->nullable()->after('level')
                    ->constrained('opd')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['id_opd']);
            $table->dropColumn('id_opd');
        });
    }
};
