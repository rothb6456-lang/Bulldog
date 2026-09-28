<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sanctum's original migration used an integer morph key, while this
     * application uses UUID primary keys for users. Keep existing token rows
     * intact while making new personal access tokens UUID-compatible.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->uuid('tokenable_id')->change();
        });
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->unsignedBigInteger('tokenable_id')->change();
        });
    }
};
