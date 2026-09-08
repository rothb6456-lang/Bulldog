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
        Schema::create('game_roster_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('game_id')->index();
            $table->foreign('game_id')->references('id')->on('games')->onDelete('cascade');

            $table->uuid('team_id')->index();
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');

            $table->uuid('player_identity_id')->index();
            $table->foreign('player_identity_id')->references('id')->on('player_identities')->onDelete('restrict');

            $table->uuid('team_membership_id')->nullable()->index();
            $table->foreign('team_membership_id')->references('id')->on('team_memberships')->onDelete('set null');

            $table->string('roster_status')->default('active'); // active, bench, injured, absent
            $table->boolean('eligible_to_play')->default(true);
            $table->timestamps();

            $table->unique(['game_id', 'player_identity_id'], 'game_player_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_roster_entries');
    }
};
