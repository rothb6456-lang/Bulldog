<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_event_players', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('game_event_id')->index();
            $table->foreign('game_event_id')->references('id')->on('game_events')->onDelete('cascade');

            $table->uuid('player_identity_id')->index();
            $table->foreign('player_identity_id')->references('id')->on('player_identities')->onDelete('cascade');

            $table->string('role'); // 'batter', 'pitcher', 'runner', 'fielder', 'assisting_fielder'
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_event_players');
    }
};