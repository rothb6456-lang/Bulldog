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
        Schema::create('player_training_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('player_identity_id')->unique(); // 1:1 with player_identities
            $table->string('experience_level', 50)->nullable()->index(); // beginner, intermediate, advanced
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('player_identity_id')
                  ->references('id')
                  ->on('player_identities')
                  ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_training_profiles');
    }
};
