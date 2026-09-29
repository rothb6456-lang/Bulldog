<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SportSeeder extends Seeder
{
    /**
     * Seed the 'sports' table with Baseball and Softball.
     */
    public function run(): void
    {
        $rows = [
            [
                'id' => Str::uuid()->toString(),
                'code' => 'baseball',
                'name' => 'Baseball',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'code' => 'softball',
                'name' => 'Softball',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        foreach ($rows as $row) {
            \App\Models\Sport::updateOrCreate(['code' => $row['code']], ['name' => $row['name']]);
        }
    }
}