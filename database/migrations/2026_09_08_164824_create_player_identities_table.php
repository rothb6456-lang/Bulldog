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
        Schema::create('player_identities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('player_code')->unique()->index(); // e.g., PLY-X29TQ8 [6]
            $table->uuid('user_id')->nullable()->index(); // Nullable for coach-created profiles [5]
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->string('claim_status')->default('unclaimed'); // unclaimed, pending, claimed, disputed [2]
            $table->string('display_name');
            $table->integer('birth_year')->nullable();
            $table->uuid('created_by_user_id')->nullable()->index();
            $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_identities');
    }
};