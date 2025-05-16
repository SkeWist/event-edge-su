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
                'name' => 'Летний турнир 2025',
                'description' => 'Прикольный и интересный турнир',
                'start_date' => Carbon::now()->addDays(5)->format('Y-m-d H:i'),
                'end_date' => Carbon::now()->addDays(10)->format('Y-m-d H:i'),
                'user_id' => 1,
                'game_id' => 1,
                'stage_id' => 1,
                'views_count' => 0,
                'status' => 'pending',
                'image' => 'tournament_images/2.jpg',
                'created_at' => now()->format('Y-m-d H:i'),
                'updated_at' => now()->format('Y-m-d H:i'),
            ],
            [
                'name' => 'Зимний турнир 2025',
                'description' => 'Прикольный и интересный турнир по игре Dota 2',
                'start_date' => Carbon::now()->subDays(5)->format('Y-m-d H:i'),
                'end_date' => Carbon::now()->addDays(3)->format('Y-m-d H:i'),
                'user_id' => 2,
                'game_id' => 2,
                'stage_id' => 2,
                'views_count' => 2,
                'status' => 'ongoing',
                'image' => 'tournament_images/3.jpg',
                'created_at' => now()->format('Y-m-d H:i'),
                'updated_at' => now()->format('Y-m-d H:i'),
            ],
            [
                'name' => 'Standoff 2 Open Cup',
                'description' => 'Турнир по игре Standoff 2, закачаетесь!',
                'start_date' => Carbon::now()->subDays(30)->format('Y-m-d H:i'),
                'end_date' => Carbon::now()->subDays(10)->format('Y-m-d H:i'),
                'user_id' => 3,
                'game_id' => 3,
                'stage_id' => 3,
                'views_count' => 12,
                'status' => 'completed',
                'image' => 'tournament_images/4.jpg',
                'created_at' => now()->format('Y-m-d H:i'),
                'updated_at' => now()->format('Y-m-d H:i'),
            ],
            [
                'name' => 'CS2 2X2 skill cup',
                'description' => 'Турнир 2 на 2 по игре CS2!',
                'start_date' => Carbon::now()->addDays(15)->format('Y-m-d H:i'),
                'end_date' => Carbon::now()->addDays(20)->format('Y-m-d H:i'),
                'user_id' => 1,
                'game_id' => 1,
                'stage_id' => 2,
                'views_count' => 0,
                'status' => 'pending',
                'image' => 'tournament_images/5.jpg',
                'created_at' => now()->format('Y-m-d H:i'),
                'updated_at' => now()->format('Y-m-d H:i'),
            ],
            [
                'name' => 'Minecraft hunger games',
                'description' => 'Турнир по игре Minecraft!',
                'start_date' => Carbon::now()->addDays(25)->format('Y-m-d H:i'),
                'end_date' => Carbon::now()->addDays(28)->format('Y-m-d H:i'),
                'user_id' => 2,
                'game_id' => 2,
                'stage_id' => 1,
                'views_count' => 0,
                'status' => 'pending',
                'image' => 'tournament_images/6.jpg',
                'created_at' => now()->format('Y-m-d H:i'),
                'updated_at' => now()->format('Y-m-d H:i'),
            ],
            [
                'name' => 'WOT championship',
                'description' => 'Турнир по игре World of Tanks!',
                'start_date' => Carbon::now()->addDays(30)->format('Y-m-d H:i'),
                'end_date' => Carbon::now()->addDays(35)->format('Y-m-d H:i'),
                'user_id' => 3,
                'game_id' => 3,
                'stage_id' => 3,
                'views_count' => 0,
                'status' => 'pending',
                'image' => 'tournament_images/7.jpg',
                'created_at' => now()->format('Y-m-d H:i'),
                'updated_at' => now()->format('Y-m-d H:i'),
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
