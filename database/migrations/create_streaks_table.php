<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('streaks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('subject_type'); // 'player', 'team'
            $table->uuid('subject_id')->index();

            $table->string('streak_type');  // 'hitting_streak', 'on_base_streak', 'winning_streak'
            $table->integer('current_value')->default(0);
            $table->integer('best_value')->default(0);

            $table->uuid('start_game_id')->nullable();
            $table->uuid('end_game_id')->nullable(); // Null if streak is currently active
            $table->string('official_status')->default('official');
            $table->timestamps();

            $table->index(['subject_type', 'subject_id', 'streak_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('streaks');
    }
};