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
        Schema::create('team_role_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('team_id')->index();
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            
            $table->uuid('user_id')->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->string('role_type'); // team_admin, coach, scorekeeper, viewer
            $table->uuid('granted_by_user_id')->nullable();
            $table->foreign('granted_by_user_id')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
            
            $table->unique(['team_id', 'user_id', 'role_type'], 'team_user_role_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_role_assignments');
    }
};