<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('permohonan_histori', function (Blueprint $table) {
            if (!Schema::hasColumn('permohonan_histori', 'role_operator')) {
                $table->string('role_operator')->nullable()->after('id_operator')
                    ->comment('Role user saat melakukan aksi (untuk identitas approver/rejector)');
            }
        });
    }

    public function down(): void
    {
        Schema::table('permohonan_histori', function (Blueprint $table) {
            $table->dropColumn('role_operator');
        });
    }
};
