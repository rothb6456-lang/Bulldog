<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('achievement_awards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('achievement_definition_id')->index();
            $table->foreign('achievement_definition_id')->references('id')->on('achievement_definitions')->onDelete('cascade');

            $table->string('subject_type'); // 'player', 'team'
            $table->uuid('subject_id')->index();

            $table->string('official_status')->default('official');
            $table->string('source_type')->default('bulldog_derived');
            $table->json('metadata_json')->nullable(); // Contextual references (e.g., game_id)
            $table->timestamp('awarded_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('achievement_awards');
    }
};