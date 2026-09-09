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
        Schema::create('training_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('player_identity_id')->index();
            $table->uuid('phase_id')->nullable()->index();
            $table->date('session_date')->index();
            $table->integer('phase_week')->nullable();
            $table->decimal('program_day', 3, 1)->nullable(); // e.g., 1.0, 2.0, 3.0, 4.0
            $table->string('workout_name');
            $table->timestamp('session_start_time')->nullable();
            $table->timestamp('session_end_time')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->string('gym_location')->nullable();
            $table->decimal('bodyweight_lbs', 5, 2)->nullable();
            $table->decimal('rpe_overall', 3, 1)->nullable();
            $table->integer('exercise_count')->default(0);
            $table->text('general_notes')->nullable();
            $table->timestamps();

            $table->foreign('player_identity_id')
                  ->references('id')
                  ->on('player_identities')
                  ->cascadeOnDelete();

            $table->foreign('phase_id')
                  ->references('id')
                  ->on('training_phases')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_sessions');
    }
};