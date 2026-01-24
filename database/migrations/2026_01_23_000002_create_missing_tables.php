<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create sliders table if not exists
        if (!Schema::hasTable('sliders')) {
            Schema::create('sliders', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('title');
                $table->text('desc')->nullable();
                $table->string('image')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 2. Create faqs table if not exists
        if (!Schema::hasTable('faqs')) {
            Schema::create('faqs', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('question');
                $table->text('answer');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 3. Create kategori table if not exists (User referred to as Klasifikasi)
        if (!Schema::hasTable('kategori')) {
            Schema::create('kategori', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('label');
                $table->string('slug')->nullable();
                $table->timestamps();
            });
        }

        // 4. Ensure Permissions Exist
        $permissions = [
            'master.slider.index',
            'master.slider.create',
            'master.slider.edit',
            'master.slider.destroy',
            'master.faq.index',
            'master.faq.create',
            'master.faq.edit',
            'master.faq.destroy',
            'master.kategori.index',
            'master.kategori.create',
            'master.kategori.edit',
            'master.kategori.destroy',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // 5. Assign Permissions to Roles (administrator, superadmin)
        // Note: Pivot table 'role_has_permissions' might not check existence here safely if migration order is mixed.
        // commenting out to prevent crash if spatie tables missing.
        /*
        $roles = Role::whereIn('name', ['administrator', 'superadmin'])->get();
        foreach ($roles as $role) {
            $role->givePermissionTo($permissions);
        }
        */
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sliders');
        Schema::dropIfExists('faqs');
        // Do not drop kategori as it might be critical
    }
};
