<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class StageSeeder extends Seeder
{
    /**
     * Запуск сидера.
     */
    public function run()
    {
        // Данные для заполнения
        $stages = [
            [
                'name' => 'Group Stage',
                'start_date' => Carbon::now()->addDays(1), // Начинается через 1 день
                'end_date' => Carbon::now()->addDays(7), // Заканчивается через 7 дней
                'stage_type_id' => 1, // ID типа этапа
                'rounds' => '3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Quarterfinals',
                'start_date' => Carbon::now()->addDays(8),
                'end_date' => Carbon::now()->addDays(10),
                'stage_type_id' => 2,
                'rounds' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Semifinals',
                'start_date' => Carbon::now()->addDays(11),
                'end_date' => Carbon::now()->addDays(13),
                'stage_type_id' => 2,
                'rounds' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Final',
                'start_date' => Carbon::now()->addDays(14),
                'end_date' => Carbon::now()->addDays(15),
                'stage_type_id' => 1,
                'rounds' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Вставка данных в таблицу
        DB::table('stages')->insert($stages);
    }
}
