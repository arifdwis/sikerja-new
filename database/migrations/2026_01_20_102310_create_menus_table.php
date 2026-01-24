<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('menus')->onDelete('cascade');
            $table->string('title');
            $table->string('route')->nullable()->comment('Route name or URL');
            $table->string('icon')->nullable();
            $table->string('permission_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->string('roles')->nullable()->comment('Comma separated roles, or specific category like "pemohon"');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
