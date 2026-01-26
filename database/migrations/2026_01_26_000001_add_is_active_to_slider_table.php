<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('slider', function (Blueprint $table) {
            if (!Schema::hasColumn('slider', 'desc')) {
                $table->text('desc')->nullable()->after('label');
            }
            if (!Schema::hasColumn('slider', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('kategori');
            }
        });
    }

    public function down()
    {
        Schema::table('slider', function (Blueprint $table) {
            $table->dropColumn(['desc', 'is_active']);
        });
    }
};
