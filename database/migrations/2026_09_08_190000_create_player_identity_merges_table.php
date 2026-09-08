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
        Schema::create('player_identity_merges', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // The surviving profile that will inherit the history and associations
            $table->uuid('canonical_identity_id')->index();
            $table->foreign('canonical_identity_id')->references('id')->on('player_identities')->onDelete('cascade');

            // The duplicate profile that will be absorbed and deactivated/deleted
            $table->uuid('duplicate_identity_id')->index();

            // Captured state details for historical backup before deconstruction
            $table->string('duplicate_player_code');
            $table->string('duplicate_display_name');
            $table->json('merged_records_manifest'); // Log of re-targeted database records

            $table->uuid('merged_by_user_id')->nullable();
            $table->foreign('merged_by_user_id')->references('id')->on('users')->onDelete('set null');

            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_identity_merges');
    }
};