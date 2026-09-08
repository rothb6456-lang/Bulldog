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
        Schema::create('games', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('game_code')->unique()->index(); // e.g., GM-20260908-XYZ
            $table->uuid('sport_id')->index();
            $table->foreign('sport_id')->references('id')->on('sports')->onDelete('restrict');

            $table->uuid('home_team_id')->index();
            $table->foreign('home_team_id')->references('id')->on('teams')->onDelete('cascade');

            $table->uuid('away_team_id')->index();
            $table->foreign('away_team_id')->references('id')->on('teams')->onDelete('cascade');

            $table->timestamp('scheduled_at')->nullable();
            $table->string('location')->nullable();

            $table->uuid('ruleset_id')->nullable()->index();
            $table->foreign('ruleset_id')->references('id')->on('rulesets')->onDelete('set null');

            $table->string('status')->default('draft'); // draft, in_progress, review, finalized, corrected, suspended
            $table->uuid('ownership_team_id')->index(); // Defaults to home_team_id per MVP specifications
            $table->foreign('ownership_team_id')->references('id')->on('teams')->onDelete('cascade');

            $table->uuid('created_by_user_id')->nullable()->index();
            $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('set null');

            $table->timestamp('finalized_at')->nullable();
            $table->uuid('finalized_by_user_id')->nullable()->index();
            $table->foreign('finalized_by_user_id')->references('id')->on('users')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
