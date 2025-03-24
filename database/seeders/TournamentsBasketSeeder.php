<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TournamentBasket;
use App\Models\Tournament;
use App\Models\GameMatch;
use App\Models\Team;

class TournamentsBasketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tournaments = Tournament::all();
        $matches = GameMatch::all();
        $teams = Team::all();

        if ($tournaments->isEmpty() || $matches->isEmpty() || $teams->count() < 2) {
            $this->command->warn('Недостаточно данных для сидирования турнирной сетки.');
            return;
        }

        foreach ($tournaments as $tournament) {
            foreach ($matches->random(min(3, $matches->count())) as $match) {
                TournamentBasket::create([
                    'tournament_id' => $tournament->id,
                    'game_match_id' => $match->id,
                    'status' => ['pending', 'completed', 'canceled'][rand(0, 2)],
                    'result' => rand(0, 1) ? rand(0, 5) . ':' . rand(0, 5) : null,
                ]);
            }
        }
    }
}
