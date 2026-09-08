<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('season_aggregates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('subject_type'); // 'player', 'team', 'coach' [141]
            $table->uuid('subject_id')->index();
            $table->string('season_label')->index(); // e.g., 'Summer 2026' [152]

            $table->string('stat_key');   // e.g., 'AB', 'H', 'HR', 'RBI'
            $table->decimal('stat_value', 8, 2)->default(0.00);

            // Tracks whether any part of this season contains unverified imported data
            $table->string('source_type')->default('bulldog_derived'); // 'bulldog_derived', 'uploaded_import', 'manual_historical', 'mixed' [150]
            $table->string('fidelity_level')->default('event_complete'); // 'season_totals', 'box_score', 'game_log', 'event_complete' [150]

            $table->timestamps();

            $table->unique(['subject_type', 'subject_id', 'season_label', 'stat_key'], 'subject_season_stat_unique');
        });

        Schema::create('career_aggregates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('subject_type'); // 'player', 'team', 'coach' [141]
            $table->uuid('subject_id')->index();

            $table->string('stat_key');
            $table->decimal('stat_value', 10, 2)->default(0.00);

            $table->string('source_type')->default('bulldog_derived');
            $table->string('fidelity_level')->default('event_complete');

            $table->timestamps();

            $table->unique(['subject_type', 'subject_id', 'stat_key'], 'subject_career_stat_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('season_aggregates');
        Schema::dropIfExists('career_aggregates');
    }
};