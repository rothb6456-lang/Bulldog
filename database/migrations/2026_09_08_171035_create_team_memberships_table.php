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
        Schema::create('team_memberships', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('team_id')->index();
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            
            // Relates to PlayerIdentity. Nullable to support non-player staff memberships
            $table->uuid('player_identity_id')->nullable()->index();
            $table->foreign('player_identity_id')->references('id')->on('player_identities')->onDelete('set null');
            
            $table->string('membership_type')->default('player'); // player, coach, manager, assistant
            $table->string('jersey_number')->nullable();
            $table->text('positions_json')->nullable(); // JSON list of active field positions: e.g., ["SS", "P"]
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->default('active'); // active, inactive
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_memberships');
    }
};