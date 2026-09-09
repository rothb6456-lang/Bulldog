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
        Schema::create('exercise_name_maps', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('original_name')->unique()->index();
            $table->uuid('exercise_id');
            $table->timestamps();

            $table->foreign('exercise_id')
                  ->references('id')
                  ->on('exercises')
                  ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercise_name_maps');
    }
};