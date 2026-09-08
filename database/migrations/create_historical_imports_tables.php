<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historical_imports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('import_type'); // 'season_stats', 'game_log', 'team_history' [269]
            $table->string('context_type'); // 'player', 'team', 'league' [142]
            $table->uuid('context_id')->index();

            $table->string('source_label'); // e.g., 'GameChanger CSV', 'Paper Scorebook Manual Entry' [142, 148]
            $table->string('fidelity_level')->default('season_totals'); // 'season_totals', 'box_score', 'game_log' [150]
            $table->string('verification_status')->default('pending'); // 'pending', 'approved', 'rejected'

            $table->uuid('uploaded_by_user_id')->nullable();
            $table->foreign('uploaded_by_user_id')->references('id')->on('users')->onDelete('set null');

            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('imported_stat_lines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('historical_import_id')->index();
            $table->foreign('historical_import_id')->references('id')->on('historical_imports')->onDelete('cascade');

            $table->string('subject_type')->default('player'); // 'player' or 'team'
            $table->uuid('subject_id')->index(); // player_identity_id or team_id

            $table->string('season_key'); // e.g., 'Spring 2025' [269]
            $table->date('game_date')->nullable(); // Null if season-total fidelity

            // Flexible schema to store the variable columns of uploaded tables safely [142, 152]
            $table->json('stat_blob_json');

            $table->string('source_row_identifier')->nullable(); // Track row indexes for auditing [269]
            $table->timestamps();
        });

        Schema::create('import_audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('historical_import_id')->index();
            $table->uuid('user_id')->nullable();
            $table->string('action'); // 'uploaded', 'previewed', 'confirmed', 'voided'
            $table->json('metadata_json')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_audit_logs');
        Schema::dropIfExists('imported_stat_lines');
        Schema::dropIfExists('historical_imports');
    }
};