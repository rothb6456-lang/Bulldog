<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exercises', function (Blueprint $table) {
            $table->string('muscle_group')->nullable()->after('movement_pattern');
            $table->string('category')->nullable()->after('muscle_group');
            $table->string('equipment_type')->nullable()->after('category');
            $table->boolean('is_unilateral')->default(false)->after('equipment_type');
            $table->boolean('is_timed')->default(false)->after('is_unilateral');
            $table->text('safety_notes')->nullable()->after('is_timed');
        });
    }

    public function down(): void
    {
        Schema::table('exercises', function (Blueprint $table) {
            $table->dropColumn([
                'muscle_group',
                'category',
                'equipment_type',
                'is_unilateral',
                'is_timed',
                'safety_notes',
            ]);
        });
    }
};
