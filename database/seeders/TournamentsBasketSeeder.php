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
        $tournament = Tournament::first();
        if (!$tournament) {
            $this->command->warn('Нет турнира для создания сетки.');
            return;
        }

        // Получаем все матчи турнира
        $matches = GameMatch::where('tournament_id', $tournament->id)
            ->orderBy('stage_id')
            ->get();

        if ($matches->count() < 7) { // 4 четвертьфинала + 2 полуфинала + 1 финал
            $this->command->warn('Недостаточно матчей для создания турнирной сетки.');
            return;
        }

        // Создаем записи в турнирной сетке для каждого матча
        foreach ($matches as $match) {
            TournamentBasket::create([
                'tournament_id' => $tournament->id,
                'game_match_id' => $match->id,
                'status' => $match->status,
                'result' => $match->result,
            ]);
        }

        $this->command->info('Турнирная сетка успешно создана.');
    }
}
