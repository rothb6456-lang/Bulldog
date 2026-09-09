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
        Schema::create('training_sets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('session_id')->index();
            $table->uuid('exercise_id')->index();
            $table->integer('set_number');
            $table->decimal('weight_lbs', 6, 2)->default(0.00);
            $table->decimal('reps', 5, 1)->nullable(); // supports half-reps e.g., 5.5
            $table->decimal('duration_seconds', 8, 2)->nullable(); // for timed holds, carries, cardio
            $table->decimal('distance_meters', 8, 2)->nullable(); // for rowing, treadmill
            $table->string('rir', 20)->nullable(); // e.g., "1-2", "0-1", "2+"
            $table->string('tempo', 20)->nullable(); // e.g., "3-1-2", "2c-1p-3e"
            $table->string('superset_group')->nullable();
            $table->text('set_notes')->nullable();
            $table->timestamps();

            $table->foreign('session_id')
                  ->references('id')
                  ->on('training_sessions')
                  ->cascadeOnDelete();

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
        Schema::dropIfExists('training_sets');
    }
};