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
        Schema::create('defensive_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('game_id')->index();
            $table->foreign('game_id')->references('id')->on('games')->onDelete('cascade');

            $table->uuid('team_id')->index();
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');

            $table->uuid('player_identity_id')->index();
            $table->foreign('player_identity_id')->references('id')->on('player_identities')->onDelete('restrict');

            $table->string('position_code'); // P, C, 1B, 2B, 3B, SS, LF, CF, RF, EH, SUB
            $table->uuid('effective_from_event_id')->nullable()->index(); // Nullable initially before event logging
            $table->uuid('effective_to_event_id')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('defensive_assignments');
    }
};