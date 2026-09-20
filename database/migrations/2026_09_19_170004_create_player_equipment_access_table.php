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
        Schema::create('player_equipment_access', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('player_identity_id')->index();
            $table->string('equipment_type', 100)->index(); // matches exercises.equipment_type vocabulary
            $table->string('facility_label')->nullable(); // e.g., "Home", "Planet Fitness" -- informational only
            $table->timestamps();

            $table->foreign('player_identity_id')
                  ->references('id')
                  ->on('player_identities')
                  ->cascadeOnDelete();

            // Hard dedup guard: no historical value in keeping duplicate equipment rows
            $table->unique(['player_identity_id', 'equipment_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_equipment_access');
    }
};
