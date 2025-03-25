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
        $tournaments = Tournament::take(3)->get();
        $matches = GameMatch::take(3)->get();

        if ($tournaments->count() < 3 || $matches->count() < 3) {
            $this->command->warn('Недостаточно данных для сидирования турнирной сетки.');
            return;
        }

        foreach ($tournaments as $index => $tournament) {
            TournamentBasket::create([
                'tournament_id' => $tournament->id,
                'game_match_id' => $matches[$index]->id,
                'status' => ['pending', 'completed', 'canceled'][rand(0, 2)],
                'result' => rand(0, 1) ? rand(0, 5) . ':' . rand(0, 5) : null,
            ]);
        }
    }
}
