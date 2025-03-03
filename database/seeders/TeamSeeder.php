<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TeamSeeder extends Seeder
{
    /**
     * Запуск сидера.
     */
    public function run()
    {
        // Данные для заполнения
        $teams = [
            [
                'name' => 'Team Alpha',
                'captain_id' => 1, // ID капитана (пользователя)
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Team Bravo',
                'captain_id' => 2,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Team Charlie',
                'captain_id' => 1,
                'status' => 'inactive',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Team Delta',
                'captain_id' => 3,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Вставка данных в таблицу
        DB::table('teams')->insert($teams);
    }
}
