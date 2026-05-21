<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chatbot_feedback_logs', function (Blueprint $table) {
            $table->id();
            $table->text('question');
            $table->longText('answer')->nullable();
            $table->string('confidence', 20)->default('rendah');
            $table->json('context_ids')->nullable();
            $table->string('status', 30)->default('needs_review');
            $table->string('failure_reason', 191)->nullable();
            $table->string('source', 50)->default('landing_widget');
            $table->timestamps();

            $table->index(['status', 'confidence']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatbot_feedback_logs');
    }
};
