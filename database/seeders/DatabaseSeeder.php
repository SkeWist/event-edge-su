<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Team;
use App\Models\Game;
use App\Models\Stage;
use App\Models\StageType;
use App\Models\Tournament;
use App\Models\Participant;
use App\Models\NewsFeed;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Создание ролей
        $this->call(RoleSeeder::class);
        // Создание пользователей
        $this->call(UserSeeder::class);
        // Создание игр
        $this->call(GameSeeder::class);
        // Создание типов стадий
        $this->call(StageTypeSeeder::class);
        // Создание команд
        $this->call(TeamSeeder::class);
        // Создание стадий
        $this->call(StageSeeder::class);
        // Создание турнира
        $this->call(TournamentsTableSeeder::class);
        // Создание участников
        $this->call(ParticipantSeeder::class);
        // Создание новостей
        $this->call(NewsFeedSeeder::class);
        // Создание игровых матчей
        $this->call(GameMatchSeeder::class);
        // Создание турнирной сетки
        $this->call(TournamentsBasketSeeder::class);
    }
}
