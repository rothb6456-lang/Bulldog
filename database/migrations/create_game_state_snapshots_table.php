<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_state_snapshots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('game_id')->index();
            $table->foreign('game_id')->references('id')->on('games')->onDelete('cascade');

            $table->uuid('game_event_id')->nullable()->index(); // Null for pre-game initialized state
            $table->foreign('game_event_id')->references('id')->on('game_events')->onDelete('cascade');

            $table->integer('sequence_number')->default(0);
            $table->integer('inning_number')->default(1);
            $table->string('half_inning')->default('top');
            $table->integer('outs')->default(0);
            $table->integer('balls')->default(0);
            $table->integer('strikes')->default(0);
            $table->integer('score_home')->default(0);
            $table->integer('score_away')->default(0);
            $table->string('base_state')->default('000'); // '100', '110', '111', etc.

            $table->uuid('current_batter_id')->nullable();
            $table->uuid('current_pitcher_id')->nullable();

            // JSON payloads to persist lineup pointers (batting order indexing) and live field charts
            $table->json('lineup_pointers')->nullable();
            $table->json('defensive_alignment')->nullable();

            $table->timestamps();

            $table->unique(['game_id', 'sequence_number'], 'game_snapshot_sequence_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_state_snapshots');
    }
};