<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    // Runs before the existing upgrade on fresh databases; harmless when that
    // upgrade already ran. SQLite cannot drop an indexed column.
    public function up(): void
    {
        if (Schema::hasColumn('player_equipment_access', 'equipment_type') && Schema::hasIndex('player_equipment_access', 'player_equipment_access_equipment_type_index')) {
            Schema::table('player_equipment_access', fn (Blueprint $table) => $table->dropIndex('player_equipment_access_equipment_type_index'));
        }
    }
    public function down(): void {}
};
