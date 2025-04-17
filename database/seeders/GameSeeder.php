<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GameSeeder extends Seeder
{
    /**
     * Запуск сидера.
     */
    public function run()
    {
        DB::table('games')->insert([
            [
                'name' => 'Valorant',
                'logo' => '/gameLogo/valorant.png',
                'description' => 'Это онлайн-шутер от первого лица, в котором игроки берут под управление одного из множества персонажей с уникальными способностями и делятся на две команды по пять человек. Одна должна заминировать точку, вторая - ее защитить.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Counter-strike 2',
                'logo' => '/gameLogo/cs2.jpg',
                'description' => 'Многопользовательская компьютерная игра в жанре шутера от первого лица, разработанная и выпущенная для Windows американской компанией Valve.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Counter-strike 1.6',
                'logo' => '/gameLogo/cs1.6.jpg',
                'description' => 'Многопользовательская компьютерная игра в жанре шутера от первого лица, разработанная и выпущенная для Windows американской компанией Valve.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Tekken 7',
                'logo' => '/gameLogo/tekken.jpg',
                'description' => 'Компьютерная игра в жанре файтинг, седьмая основная часть игровой серии Tekken. Выпущена для аркадных автоматов 18 марта 2015 года, после чего 2 июня 2017 года она была портирована на PlayStation 4, Xbox One и Windows.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Mortal Combat X',
                'logo' => '/gameLogo/mortalCombat.jpg',
                'description' => 'Десятая часть серии файтингов Mortal Kombat. Выпущена в 2015 году для платформ PC, Xbox One, Xbox 360, Playstation 4 и Playstation 3.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Dota 2',
                'logo' => '/gameLogo/dota.png',
                'description' => 'Компьютерная многопользовательская командная игра жанра Action RTS, разрабатываемая компанией Valve Corporation. Игра была анонсирована 13 октября 2010 года, а выпущена 9 июля 2013 года для Windows, а для Linux и OS X 18 июля 2013 года.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'League of Legends',
                'logo' => '/gameLogo/lol.png',
                'description' => 'Многопользовательская компьютерная игра в жанре MOBA, разработанная и выпущенная американской компанией Riot Games в 2009 году для платформ Microsoft Windows и macOS.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Другая игра',
                'logo' => '',
                'description' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
