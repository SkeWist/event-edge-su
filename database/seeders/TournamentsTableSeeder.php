<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TournamentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Данные для заполнения
        $tournaments = [
            [
                'name' => 'Summer Tournament 2023',
                'description' => 'Annual summer gaming tournament.',
                'start_date' => Carbon::now()->addDays(5), // Начинается через 5 дней
                'end_date' => Carbon::now()->addDays(10), // Заканчивается через 10 дней
                'user_id' => 1, // ID пользователя, создавшего турнир
                'game_id' => 1, // ID игры
                'stage_id' => 1, // ID этапа
                'views_count' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Winter Championship 2023',
                'description' => 'Competitive winter gaming championship.',
                'start_date' => Carbon::now()->addDays(15),
                'end_date' => Carbon::now()->addDays(20),
                'user_id' => 2,
                'game_id' => 2,
                'stage_id' => 2,
                'views_count' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Spring Showdown 2023',
                'description' => 'Exciting spring gaming event.',
                'start_date' => Carbon::now()->addDays(25),
                'end_date' => Carbon::now()->addDays(30),
                'user_id' => 3,
                'game_id' => 3,
                'stage_id' => 3,
                'views_count' => 12,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Вставка данных в таблицу
        DB::table('tournaments')->insert($tournaments);
    }
}
