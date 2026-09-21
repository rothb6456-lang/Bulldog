<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Two migrations (000001 and 000010) independently added overlapping
     * metadata to `exercises` — an older set the live Filament form actually
     * edits (muscle_group, category, equipment_type, is_unilateral, is_timed,
     * safety_notes) and a newer, richer set nothing ever wrote to
     * (primary_muscle, secondary_muscles, default_equipment_id, laterality,
     * exercise_category, is_time_based, is_distance_based, shoulder_safety_notes).
     * This consolidates onto one source of truth: the newer set survives
     * (Filament is updated separately to match), and primary_muscle /
     * secondary_muscles / default_equipment_id are further replaced here by a
     * real equipment_id FK and the exercise_body_structures pivot.
     */
    public function up(): void
    {
        Schema::table('exercises', function (Blueprint $table) {
            $table->uuid('equipment_id')->nullable()->after('movement_pattern');
            $table->foreign('equipment_id')->references('id')->on('equipment')->nullOnDelete();
        });

        Schema::table('exercises', function (Blueprint $table) {
            $table->dropColumn([
                'muscle_group',
                'category',
                'equipment_type',
                'is_unilateral',
                'is_timed',
                'safety_notes',
                'primary_muscle',
                'secondary_muscles',
                'default_equipment_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('exercises', function (Blueprint $table) {
            $table->dropForeign(['equipment_id']);
            $table->dropColumn('equipment_id');
            $table->string('muscle_group')->nullable();
            $table->string('category')->nullable();
            $table->string('equipment_type')->nullable();
            $table->boolean('is_unilateral')->default(false);
            $table->boolean('is_timed')->default(false);
            $table->text('safety_notes')->nullable();
            $table->string('primary_muscle')->nullable();
            $table->string('secondary_muscles')->nullable();
            $table->string('default_equipment_id')->nullable();
        });
    }
};
