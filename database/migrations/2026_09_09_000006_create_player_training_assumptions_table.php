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
        Schema::create('player_training_assumptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('player_identity_id')->index();
            $table->string('assumption_code', 50)->nullable(); // e.g., TA-001
            $table->string('category', 100)->index(); // Shoulder, Grip, Movement Pattern, Recovery, Equipment
            $table->text('description');
            $table->string('confidence', 50)->default('Medium'); // High, Medium, Low
            $table->date('first_observed')->nullable();
            $table->date('last_validated')->nullable();
            $table->string('status', 50)->default('active')->index(); // active, resolved, archived
            $table->text('supporting_evidence')->nullable();
            $table->uuid('phase_id_first_observed')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('player_identity_id')
                  ->references('id')
                  ->on('player_identities')
                  ->cascadeOnDelete();

            $table->foreign('phase_id_first_observed')
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
        Schema::dropIfExists('player_training_assumptions');
    }
};