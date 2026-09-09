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
        Schema::create('player_prs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('player_identity_id')->index();
            $table->uuid('exercise_id')->index();
            $table->string('pr_code', 50)->nullable(); // e.g., PR-001
            $table->string('pr_type', 100)->index(); // Heaviest Weight, Longest Duration, Best Time
            $table->decimal('pr_value', 8, 2);
            $table->string('pr_unit', 20); // lbs, seconds, meters
            $table->date('pr_date')->index();
            $table->uuid('phase_id')->nullable();
            $table->uuid('session_id')->nullable();
            $table->decimal('previous_best', 8, 2)->nullable();
            $table->date('previous_best_date')->nullable();
            $table->string('set_details')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('player_identity_id')
                  ->references('id')
                  ->on('player_identities')
                  ->cascadeOnDelete();

            $table->foreign('exercise_id')
                  ->references('id')
                  ->on('exercises')
                  ->cascadeOnDelete();

            $table->foreign('phase_id')
                  ->references('id')
                  ->on('training_phases')
                  ->nullOnDelete();

            $table->foreign('session_id')
                  ->references('id')
                  ->on('training_sessions')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_prs');
    }
};