<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // MySQL DDL is not transactional; resume safely after a partial failure.
        if (! Schema::hasColumn('games', 'is_demo')) Schema::table('games', function (Blueprint $table) {
            $table->boolean('is_demo')->default(false)->index();
        });
        if (! Schema::hasTable('guardian_relationships')) Schema::create('guardian_relationships', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('player_identity_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('team_id')->constrained()->cascadeOnDelete();
            $table->string('invited_email');
            $table->foreignUuid('guardian_user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('invited_by_user_id')->constrained('users');
            $table->foreignUuid('confirmed_by_user_id')->nullable()->constrained('users');
            $table->string('verification_status')->default('invited');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();
            $table->unique(['player_identity_id', 'invited_email']);
        });
        if (! Schema::hasIndex('guardian_relationships', ['guardian_user_id', 'verification_status'])) {
            Schema::table('guardian_relationships', function (Blueprint $table) {
                $table->index(['guardian_user_id', 'verification_status'], 'guardian_access_index');
            });
        }
        if (! Schema::hasTable('momentum_launch_codes')) Schema::create('momentum_launch_codes', function (Blueprint $table) {
            $table->string('code_hash', 64)->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('expires_at');
        });
        // These historical source files have no .php extension and were never
        // discoverable by Laravel. Preserve them and apply their schema once.
        foreach (['share_links', 'timeline_entries'] as $table) {
            if (! Schema::hasTable($table)) {
                (require __DIR__.'/create_'.$table.'_table')->up();
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('momentum_launch_codes');
        Schema::dropIfExists('guardian_relationships');
        Schema::table('games', fn (Blueprint $table) => $table->dropColumn('is_demo'));
        // Existing sharing/timeline data is deliberately preserved on rollback.
    }
};
