<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StageTypeSeeder extends Seeder
{
    /**
     * Запуск сидера.
     */
    public function run(): void
    {
        DB::table('stage_types')->insert([
            ['name' => 'Single eliminate'],
            ['name' => 'Double eliminate'],
        ]);
    }
}
