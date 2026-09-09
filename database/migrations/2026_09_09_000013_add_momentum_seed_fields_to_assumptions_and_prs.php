<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('player_training_assumptions', function (Blueprint $table) {
            $table->string('confidence_level', 50)->nullable()->after('confidence');
            $table->integer('phase_first_observed')->nullable()->after('confidence_level');
        });

        Schema::table('player_prs', function (Blueprint $table) {
            $table->integer('phase_number')->nullable()->after('pr_date');
            $table->decimal('previous_best_value', 8, 2)->nullable()->after('previous_best');
        });
    }

    public function down(): void
    {
        Schema::table('player_training_assumptions', function (Blueprint $table) {
            $table->dropColumn(['confidence_level', 'phase_first_observed']);
        });

        Schema::table('player_prs', function (Blueprint $table) {
            $table->dropColumn(['phase_number', 'previous_best_value']);
        });
    }
};
