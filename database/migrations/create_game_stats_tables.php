<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_player_stats', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('game_id')->index();
            $table->foreign('game_id')->references('id')->on('games')->onDelete('cascade');

            $table->uuid('player_identity_id')->index();
            $table->foreign('player_identity_id')->references('id')->on('player_identities')->onDelete('cascade');

            $table->uuid('team_id')->index();
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');

            $table->string('stat_key'); // 'AB', 'H', 'R', 'RBI', 'BB', 'SO', 'IP_outs', 'ER', etc.
            $table->decimal('stat_value', 8, 2)->default(0.00);
            $table->timestamps();

            $table->unique(['game_id', 'player_identity_id', 'stat_key'], 'player_game_stat_unique');
        });

        Schema::create('game_team_stats', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('game_id')->index();
            $table->foreign('game_id')->references('id')->on('games')->onDelete('cascade');

            $table->uuid('team_id')->index();
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');

            $table->string('stat_key'); // 'AB', 'H', 'R', 'RBI', 'BB', 'SO', etc.
            $table->decimal('stat_value', 8, 2)->default(0.00);
            $table->timestamps();

            $table->unique(['game_id', 'team_id', 'stat_key'], 'team_game_stat_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_player_stats');
        Schema::dropIfExists('game_team_stats');
    }
};