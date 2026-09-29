<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('training_sessions', function (Blueprint $table) {
            $table->string('client_session_id', 100)->nullable();
            $table->unique(['player_identity_id', 'client_session_id'], 'training_client_session_unique');
        });
    }
    public function down(): void
    {
        Schema::table('training_sessions', function (Blueprint $table) {
            $table->dropUnique('training_client_session_unique');
            $table->dropColumn('client_session_id');
        });
    }
};
