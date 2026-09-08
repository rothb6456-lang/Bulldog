<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('record_scope');  // 'personal', 'team', 'season', 'career'
            $table->string('subject_type');  // 'player', 'team'
            $table->uuid('subject_id')->index();

            $table->string('stat_key');      // e.g., 'HR', 'SO_pitching', 'RBI'
            $table->decimal('record_value', 8, 2);

            $table->uuid('originating_game_id')->nullable(); // Game where record was set
            $table->string('official_status')->default('official');
            $table->string('source_type')->default('bulldog_derived');
            $table->json('metadata_json')->nullable();
            $table->timestamp('effective_date');
            $table->timestamps();

            $table->index(['subject_type', 'subject_id', 'stat_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('records');
    }
};