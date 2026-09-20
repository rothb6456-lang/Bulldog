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
        Schema::create('player_training_goals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('player_identity_id')->index();
            $table->uuid('phase_id')->nullable()->index(); // null = long-term / standing goal, not tied to one phase
            $table->uuid('goal_template_id')->nullable()->index(); // null = fully custom goal

            $table->string('title'); // denormalized display text: template title or custom text
            $table->text('description')->nullable();

            $table->string('target_metric_key', 100)->nullable();
            $table->decimal('target_value', 8, 2)->nullable();
            $table->string('target_unit', 20)->nullable();

            $table->date('start_date')->nullable();
            $table->date('target_date')->nullable();

            $table->string('status', 20)->default('active')->index(); // active, achieved, abandoned, expired
            $table->timestamp('achieved_at')->nullable();

            $table->string('source', 20)->default('self')->index(); // self, coach
            $table->uuid('created_by_user_id')->nullable()->index();

            $table->timestamps();

            $table->foreign('player_identity_id')
                  ->references('id')
                  ->on('player_identities')
                  ->cascadeOnDelete();

            $table->foreign('phase_id')
                  ->references('id')
                  ->on('training_phases')
                  ->nullOnDelete();

            $table->foreign('goal_template_id')
                  ->references('id')
                  ->on('training_goal_templates')
                  ->nullOnDelete();

            $table->foreign('created_by_user_id')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_training_goals');
    }
};
