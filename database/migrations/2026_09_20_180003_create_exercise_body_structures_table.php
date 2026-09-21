<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exercise_body_structures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('exercise_id');
            $table->uuid('body_structure_id');
            $table->string('role')->default('primary'); // primary | secondary
            $table->timestamps();

            $table->foreign('exercise_id')->references('id')->on('exercises')->cascadeOnDelete();
            $table->foreign('body_structure_id')->references('id')->on('body_structures')->cascadeOnDelete();
            $table->unique(['exercise_id', 'body_structure_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercise_body_structures');
    }
};
