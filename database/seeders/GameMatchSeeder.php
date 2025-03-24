<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\Tournament;
use Illuminate\Database\Seeder;
use App\Models\GameMatch;
use Carbon\Carbon;

class GameMatchSeeder extends Seeder
{
    public function run()
    {
        $tournaments = Tournament::all();
        $teams = Team::pluck('id')->toArray();

        if (count($teams) < 2) {
            $this->command->error('Недостаточно команд для создания матчей!');
            return;
        }

        foreach ($tournaments as $tournament) {
            shuffle($teams);
            $team1 = $teams[0];
            $team2 = $teams[1];

            GameMatch::create([
                'tournament_id' => $tournament->id,
                'team_1_id' => $team1,
                'team_2_id' => $team2,
                'match_date' => Carbon::now()->addDays(rand(1, 30)),
                'stage_id' => 1,
                'status' => 'pending',
                'result' => null,
                'winner_team_id' => rand(0, 1) ? $team1 : $team2, // случайный победитель
            ]);
        }
    }
}
