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
        $tournament = Tournament::first();
        $teams = Team::where('status', 'active')->take(8)->pluck('id')->toArray();

        if (count($teams) < 8) {
            $this->command->error('Недостаточно команд для создания турнирной сетки!');
            return;
        }

        // Создаем четвертьфинальные матчи
        $quarterFinals = [
            [
                'tournament_id' => $tournament->id,
                'team_1_id' => $teams[0],
                'team_2_id' => $teams[1],
                'match_date' => Carbon::now()->addDays(1),
                'stage_id' => 2,
                'status' => 'completed',
                'result' => '3:1',
                'winner_team_id' => $teams[0],
            ],
            [
                'tournament_id' => $tournament->id,
                'team_1_id' => $teams[2],
                'team_2_id' => $teams[3],
                'match_date' => Carbon::now()->addDays(1),
                'stage_id' => 2,
                'status' => 'completed',
                'result' => '2:0',
                'winner_team_id' => $teams[2],
            ],
            [
                'tournament_id' => $tournament->id,
                'team_1_id' => $teams[4],
                'team_2_id' => $teams[5],
                'match_date' => Carbon::now()->addDays(1),
                'stage_id' => 2,
                'status' => 'completed',
                'result' => '4:2',
                'winner_team_id' => $teams[4],
            ],
            [
                'tournament_id' => $tournament->id,
                'team_1_id' => $teams[6],
                'team_2_id' => $teams[7],
                'match_date' => Carbon::now()->addDays(1),
                'stage_id' => 2,
                'status' => 'completed',
                'result' => '1:0',
                'winner_team_id' => $teams[6],
            ]
        ];

        // Создаем четвертьфиналы
        foreach ($quarterFinals as $matchData) {
            GameMatch::create($matchData);
        }

        // Создаем полуфинальные матчи
        $semiFinals = [
            [
                'tournament_id' => $tournament->id,
                'team_1_id' => $teams[0], // Победитель первого четвертьфинала
                'team_2_id' => $teams[2], // Победитель второго четвертьфинала
                'match_date' => Carbon::now()->addDays(2),
                'stage_id' => 3,
                'status' => 'completed',
                'result' => '2:1',
                'winner_team_id' => $teams[0],
            ],
            [
                'tournament_id' => $tournament->id,
                'team_1_id' => $teams[4], // Победитель третьего четвертьфинала
                'team_2_id' => $teams[6], // Победитель четвертого четвертьфинала
                'match_date' => Carbon::now()->addDays(2),
                'stage_id' => 3,
                'status' => 'completed',
                'result' => '3:2',
                'winner_team_id' => $teams[4],
            ]
        ];

        // Создаем полуфиналы
        foreach ($semiFinals as $matchData) {
            GameMatch::create($matchData);
        }

        // Создаем финальный матч
        GameMatch::create([
            'tournament_id' => $tournament->id,
            'team_1_id' => $teams[0], // Победитель первого полуфинала
            'team_2_id' => $teams[4], // Победитель второго полуфинала
            'match_date' => Carbon::now()->addDays(3),
            'stage_id' => 4,
            'status' => 'completed',
            'result' => '3:1',
            'winner_team_id' => $teams[0],
        ]);

        $this->command->info('Матчи турнира успешно созданы.');
    }
}
