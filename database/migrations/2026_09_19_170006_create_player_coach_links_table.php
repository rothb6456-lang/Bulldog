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
        Schema::create('player_coach_links', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('coach_user_id')->index();
            $table->uuid('player_identity_id')->index();

            $table->string('status', 20)->default('pending')->index(); // pending, active, revoked
            $table->string('invited_by', 20)->default('coach'); // coach, athlete
            $table->timestamp('responded_at')->nullable();

            $table->timestamps();

            $table->foreign('coach_user_id')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();

            $table->foreign('player_identity_id')
                  ->references('id')
                  ->on('player_identities')
                  ->cascadeOnDelete();

            // A coach can only have one link record per athlete
            $table->unique(['coach_user_id', 'player_identity_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_coach_links');
    }
};
