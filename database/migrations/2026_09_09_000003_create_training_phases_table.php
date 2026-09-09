<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_phases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('player_identity_id')->index();
            $table->integer('phase_number')->index();
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->integer('duration_weeks')->nullable();
            $table->integer('sessions_per_week')->nullable();
            $table->text('phase_goal')->nullable();
            $table->text('key_exercises')->nullable();
            $table->text('primary_metrics')->nullable();
            $table->text('secondary_metrics')->nullable();
            $table->text('joint_notes')->nullable();
            $table->text('grip_status')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('player_identity_id')
                ->references('id')
                ->on('player_identities')
                ->cascadeOnDelete();

            $table->unique(['player_identity_id', 'phase_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_phases');
    }
};
