<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * player_equipment_access.equipment_type was a loosely-matched string
     * ("matches exercises.equipment_type vocabulary" per its own comment) built
     * before a real `equipment` table existed. Now that it does, this points
     * Momentum's equipment access at the same canonical rows the exercise
     * library uses. No live data exists in this table yet (Momentum is still
     * local-storage-only), so this is a clean replacement, not a backfill.
     *
     * custom_label stays as an escape hatch for anything a user has that isn't
     * in the canonical equipment list yet — consistent with the rest of the
     * app's philosophy of never forcing a user to wait on a curated catalog.
     */
    public function up(): void
    {
        Schema::table('player_equipment_access', function (Blueprint $table) {
            $table->uuid('equipment_id')->nullable()->after('player_identity_id');
            $table->string('custom_label')->nullable()->after('equipment_id');

            $table->foreign('equipment_id')->references('id')->on('equipment')->cascadeOnDelete();
        });

        Schema::table('player_equipment_access', function (Blueprint $table) {
            $table->dropUnique(['player_identity_id', 'equipment_type']);
            $table->dropColumn('equipment_type');
        });

        Schema::table('player_equipment_access', function (Blueprint $table) {
            // MySQL treats NULLs as distinct in a unique index, so multiple
            // custom_label rows (equipment_id NULL) never collide, while real
            // equipment_id values are still deduped per identity.
            $table->unique(['player_identity_id', 'equipment_id']);
        });
    }

    public function down(): void
    {
        Schema::table('player_equipment_access', function (Blueprint $table) {
            $table->dropUnique(['player_identity_id', 'equipment_id']);
            $table->dropForeign(['equipment_id']);
            $table->dropColumn(['equipment_id', 'custom_label']);
            $table->string('equipment_type', 100)->index();
            $table->unique(['player_identity_id', 'equipment_type']);
        });
    }
};
