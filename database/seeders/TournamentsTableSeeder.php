<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TournamentsTableSeeder extends Seeder
{
    public function run()
    {
        $tournaments = [
            [
                'name' => 'Summer Tournament 2023',
                'description' => 'Annual summer gaming tournament.',
                'start_date' => Carbon::now()->addDays(5),
                'end_date' => Carbon::now()->addDays(10),
                'user_id' => 1,
                'game_id' => 1,
                'stage_id' => 1,
                'views_count' => 0,
                'status' => 'pending', // Добавлен статус
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Winter Championship 2023',
                'description' => 'Competitive winter gaming championship.',
                'start_date' => Carbon::now()->subDays(5),
                'end_date' => Carbon::now()->addDays(3),
                'user_id' => 2,
                'game_id' => 2,
                'stage_id' => 2,
                'views_count' => 2,
                'status' => 'ongoing', // Турнир уже идет
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Spring Showdown 2023',
                'description' => 'Exciting spring gaming event.',
                'start_date' => Carbon::now()->subDays(30),
                'end_date' => Carbon::now()->subDays(10),
                'user_id' => 3,
                'game_id' => 3,
                'stage_id' => 3,
                'views_count' => 12,
                'status' => 'completed', // Турнир уже завершен
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('tournaments')->insert($tournaments);

        // Привязываем случайные команды к турнирам
        $teams = DB::table('teams')->pluck('id')->toArray();
        $tournamentIds = DB::table('tournaments')->pluck('id')->toArray();

        foreach ($tournamentIds as $tournamentId) {
            $randomTeams = array_rand($teams, min(3, count($teams)));
            foreach ((array) $randomTeams as $teamIndex) {
                DB::table('tournament_teams')->insert([
                    'tournament_id' => $tournamentId,
                    'team_id' => $teams[$teamIndex],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
