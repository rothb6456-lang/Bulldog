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
        Schema::create('player_training_guardrails', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('player_identity_id')->index();

            $table->string('body_region', 100)->index(); // shoulder, elbow, wrist, low_back, hip, knee, ankle, etc.
            $table->json('restricted_movement_patterns')->nullable(); // array of exercises.movement_pattern values to avoid/caution
            $table->string('restriction_type', 20)->default('avoid'); // avoid, modify, monitor

            $table->text('description')->nullable(); // free-text elaboration (e.g., "2019 rotator cuff repair")

            $table->string('status', 20)->default('active')->index(); // active, resolved
            $table->string('source', 20)->default('self')->index(); // self, coach
            $table->uuid('created_by_user_id')->nullable()->index();

            $table->date('first_noted_date')->nullable();
            $table->date('resolved_date')->nullable();

            $table->timestamps();

            $table->foreign('player_identity_id')
                  ->references('id')
                  ->on('player_identities')
                  ->cascadeOnDelete();

            $table->foreign('created_by_user_id')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();

            // Deliberately no unique constraint here -- guardrail history (resolved + new,
            // same body region, different cause years apart) is a legitimate use case, not a duplicate.
            // Dedup against exact ACTIVE duplicates is a UI-level concern, not a schema-level one.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_training_guardrails');
    }
};
