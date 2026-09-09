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
        Schema::create('exercises', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('canonical_name')->unique();
            $table->string('movement_pattern')->nullable()->index(); // Pull, Push, Squat/Lunge, Hip Hinge, Carry, etc.
            $table->string('primary_muscle')->nullable();
            $table->string('secondary_muscles')->nullable();
            $table->string('default_equipment_id')->nullable();
            $table->string('laterality')->default('bilateral'); // bilateral, unilateral, alternating
            $table->string('exercise_category')->index(); // Pull, Push, Legs, Arms_Biceps, Arms_Triceps, Core, Carry, Cardio, Warmup
            $table->boolean('is_time_based')->default(false);
            $table->boolean('is_distance_based')->default(false);
            $table->text('preferred_replacements')->nullable();
            $table->text('shoulder_safety_notes')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercises');
    }
};