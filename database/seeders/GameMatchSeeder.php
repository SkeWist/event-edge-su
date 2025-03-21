<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GameMatch;
use Carbon\Carbon;

class GameMatchSeeder extends Seeder
{
    /**
     * Запуск сидера.
     */
    public function run()
    {
        $matches = [
            [
                'tournament_id' => 1,
                'team_1_id' => 1,
                'team_2_id' => 2,
                'match_date' => Carbon::now()->subDays(3),
                'stage_id' => 1,
                'status' => 'completed',
                'result' => '2:1'
            ],
            [
                'tournament_id' => 1,
                'team_1_id' => 1,
                'team_2_id' => 3,
                'match_date' => Carbon::now()->subDays(2),
                'stage_id' => 1,
                'status' => 'pending',
                'result' => null
            ],
            [
                'tournament_id' => 2,
                'team_1_id' => 2,
                'team_2_id' => 4,
                'match_date' => Carbon::now()->addDays(1),
                'stage_id' => 2,
                'status' => 'pending',
                'result' => null
            ],
        ];

        foreach ($matches as $match) {
            GameMatch::create($match);
        }
    }
}
