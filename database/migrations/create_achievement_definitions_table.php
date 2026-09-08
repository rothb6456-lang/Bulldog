<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('achievement_definitions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique(); // e.g., 'CYCLE_RIDER', 'ACE_IN_HOLE'
            $table->string('name');
            $table->text('description');
            $table->string('category');       // 'performance', 'participation', 'milestone'
            $table->json('rule_json');        // Formal structure describing conditions
            $table->integer('xp_value')->default(0);
            $table->boolean('is_repeatable')->default(false);
            $table->string('visibility_default')->default('team');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('achievement_definitions');
    }
};