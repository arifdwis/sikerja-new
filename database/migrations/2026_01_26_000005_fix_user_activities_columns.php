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
        Schema::table('user_activities', function (Blueprint $table) {
            if (!Schema::hasColumn('user_activities', 'log_name')) {
                $table->string('log_name')->nullable()->index()->after('id');
            }
            // Also check for other common spatie columns just in case
            if (!Schema::hasColumn('user_activities', 'description')) {
                $table->text('description')->nullable();
            }
            if (!Schema::hasColumn('user_activities', 'subject_id')) {
                $table->nullableMorphs('subject', 'subject');
            }
            if (!Schema::hasColumn('user_activities', 'causer_id')) {
                $table->nullableMorphs('causer', 'causer');
            }
            if (!Schema::hasColumn('user_activities', 'properties')) {
                $table->json('properties')->nullable();
            }
            if (!Schema::hasColumn('user_activities', 'batch_uuid')) {
                $table->uuid('batch_uuid')->nullable();
            }
            if (!Schema::hasColumn('user_activities', 'event')) {
                $table->string('event')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_activities', function (Blueprint $table) {
            $table->dropColumn(['log_name']);
        });
    }
};
