<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // Traceable back to the source tbl_Equipment.csv (EQ-DB, EQ-BB, etc.)
            $table->string('external_code')->unique();
            $table->string('name');
            $table->string('category')->nullable()->index(); // Free Weight, Cable, Selectorized Machine, Band, Cardio, Bodyweight, Accessory
            $table->string('manufacturer')->nullable();
            $table->string('gym_location')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
