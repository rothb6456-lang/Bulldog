<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('body_structures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            // muscle | muscle_group (e.g. Rotator Cuff = 4 muscles) | region (broad grouping, e.g. Shoulders) | functional_role (e.g. Stabilizers)
            $table->string('type')->index();
            // Broad region this structure is filed under, for grouping in the UI (Chest, Back, Shoulders, Arms, Core, Hips & Legs, Full Body)
            $table->string('region')->index();
            $table->string('short_description'); // one line: what/where
            $table->text('function_notes')->nullable(); // what it does, why it matters in training
            $table->text('common_issues')->nullable(); // training-relevant injuries/considerations (general knowledge, not medical advice)
            // Intentionally left for deliberate, sourced additions later — never populated with invented citations.
            $table->text('reference_notes')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('body_structures');
    }
};
