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
        Schema::create('lineup_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('game_id')->index();
            $table->foreign('game_id')->references('id')->on('games')->onDelete('cascade');

            $table->uuid('team_id')->index();
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');

            $table->uuid('player_identity_id')->index();
            $table->foreign('player_identity_id')->references('id')->on('player_identities')->onDelete('restrict');

            $table->integer('batting_order_slot'); // 1 through 9+ (supporting continuous lineups)
            $table->string('lineup_status')->default('starter'); // starter, substitute, removed
            $table->timestamp('entered_at')->nullable();
            $table->timestamp('exited_at')->nullable();
            $table->timestamps();

            $table->unique(['game_id', 'team_id', 'batting_order_slot'], 'game_team_slot_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lineup_entries');
    }
};