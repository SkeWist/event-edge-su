<?php

namespace Database\Seeders;

use App\Models\GameMatch;
use App\Models\Tournament;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TournamentsBasketSeeder extends Seeder
{
    public function run()
    {
        // Пример: добавление нескольких матчей для турниров
        $tournaments = Tournament::all(); // Получаем все турниры
        $gameMatches = GameMatch::all(); // Получаем все игровые матчи

        // Проверяем, есть ли турниры и матчи
        if ($tournaments->isEmpty() || $gameMatches->isEmpty()) {
            $this->command->info("Нет турниров или матчей в базе данных.");
            return;
        }

        // Добавляем матчи к турнирам
        foreach ($tournaments as $tournament) {
            // Для каждого турнира добавляем случайные матчи
            $matches = $gameMatches->random(3); // Выбираем 3 случайных матча для турнира

            foreach ($matches as $match) {
                // Пример: добавление результата "win", "lose" или "draw" для матчей
                $result = ['win', 'lose', 'draw'][array_rand(['win', 'lose', 'draw'])];

                // Добавление записи в промежуточную таблицу
                $tournament->gameMatches()->attach($match->id, ['result' => $result]);
            }
        }

        $this->command->info('Таблица tournaments_basket успешно наполнена.');
    }
}
