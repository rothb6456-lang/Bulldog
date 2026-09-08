<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('game_id')->index();
            $table->foreign('game_id')->references('id')->on('games')->onDelete('cascade');

            $table->integer('sequence_number')->index();
            $table->string('event_family'); // e.g., 'plate_appearance', 'baserunning', 'pitching', 'game_admin'
            $table->string('event_type');   // e.g., 'pitch', 'single', 'walk', 'strikeout', 'stolen_base'

            // Contextual game-state snapshot prior to event
            $table->integer('inning_number');
            $table->string('half_inning'); // 'top', 'bottom'
            $table->integer('outs_before');
            $table->integer('outs_after');
            $table->integer('balls_before');
            $table->integer('strikes_before');
            $table->integer('balls_after');
            $table->integer('strikes_after');

            // Base states represented as string binary flags (e.g., '000' = empty, '101' = 1st & 3rd)
            $table->string('base_state_before')->default('000');
            $table->string('base_state_after')->default('000');

            $table->integer('score_home_before')->default(0);
            $table->integer('score_home_after')->default(0);
            $table->integer('score_away_before')->default(0);
            $table->integer('score_away_after')->default(0);

            // Flexible metadata for specialized event attributes (e.g., pitch speeds, spray vectors)
            $table->json('payload_json')->nullable();

            $table->uuid('created_by_user_id')->nullable();
            $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('set null');
            $table->boolean('is_voided')->default(false);
            $table->timestamps();

            $table->unique(['game_id', 'sequence_number'], 'game_sequence_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_events');
    }
};