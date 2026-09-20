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
        Schema::create('training_goal_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 50)->unique(); // e.g., CONSISTENCY_4WK, SQUAT_1RM_PLUS10
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category', 100)->index(); // strength, hypertrophy, consistency, mobility, endurance, return_to_play
            $table->string('metric_key', 100)->nullable(); // e.g., exercise_1rm, sessions_per_week, bodyweight -- null for qualitative goals
            $table->string('default_target_unit', 20)->nullable(); // lbs, sessions, weeks
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_goal_templates');
    }
};
