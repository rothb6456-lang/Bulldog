<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('milestones', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('subject_type'); // 'player', 'team', 'coach'
            $table->uuid('subject_id')->index();

            $table->string('milestone_type'); // e.g., 'career_hits_threshold', 'first_home_run'
            $table->string('scope_type');     // 'game', 'season', 'career'
            $table->decimal('value_reached', 8, 2);

            $table->string('official_status')->default('official'); // 'provisional', 'official', 'superseded'
            $table->string('source_type')->default('bulldog_derived'); // 'bulldog_derived', 'uploaded_import'
            $table->string('fidelity_level')->nullable(); // 'season_totals', 'play_by_play', 'event_complete'

            $table->json('metadata_json')->nullable(); // Stores context like game_id or hit details
            $table->timestamp('detected_at');
            $table->timestamps();

            $table->index(['subject_type', 'subject_id', 'milestone_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('milestones');
    }
};