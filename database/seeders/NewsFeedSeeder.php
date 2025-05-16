<?php

namespace Database\Seeders;

use App\Models\NewsCategory;
use App\Models\NewsFeed;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class NewsFeedSeeder extends Seeder
{
    /**
     * Запуск сидера.
     */
    public function run()
    {
      DB::table('news_feeds')->insert([
        [
          'title' => 'Открытие нового турнира по Dota 2',
          'slug' => 'otkrytie-turnira-dota2',
          'description' => 'Крупнейший турнир года с призовым фондом $1,000,000',
          'content' => 'Полное описание предстоящего турнира, список участников и расписание матчей...',
          'status' => NewsFeed::STATUS_PUBLISHED,
          'published_at' => Carbon::now()->subDays(2),
          'archived_at' => null,
          'user_id' => 1,
          'category_id' => 1,
          'views_count'=>0,
          'is_featured' => true,
          'image' => 'tournament_images/2.jpg',
          'meta_title' => 'Новый турнир по Dota 2 2023',
          'meta_description' => 'Анонс международного турнира по Dota 2 с крупным призовым фондом',
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now(),
        ],
        [
          'title' => 'Итоги чемпионата по CS:GO',
          'slug' => 'itogi-chempionata-csgo',
          'description' => 'Команда NAVI одержала победу на международном турнире',
          'content' => 'Подробный разбор финального матча и статистика игроков...',
          'status' => NewsFeed::STATUS_PUBLISHED,
          'published_at' => Carbon::now()->subDay(),
          'archived_at' => null,
          'user_id' => 1,
          'category_id' => 1,
          'views_count'=>0,
          'is_featured' => false,
          'image' => 'tournament_images/2.jpg',
          'meta_title' => null,
          'meta_description' => null,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now(),
        ],
        [
          'title' => 'Предстоящий баланс патч в League of Legends',
          'slug' => 'balance-patch-lol',
          'description' => 'Разработчики анонсировали изменения в следующем обновлении',
          'content' => 'Список изменений персонажей и новые предметы...',
          'status' => NewsFeed::STATUS_DRAFT,
          'published_at' => null,
          'archived_at' => null,
          'user_id' => 1,
          'views_count'=>0,
          'category_id' => 1,
          'is_featured' => false,
          'image' => 'tournament_images/2.jpg',
          'meta_title' => null,
          'meta_description' => null,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now(),
        ],
        [
          'title' => 'Архив: Результаты прошлогоднего турнира',
          'slug' => 'arhiv-rezultaty-turnira',
          'description' => 'Вспоминаем лучшие моменты турнира 2022 года',
          'content' => 'Обзор самых зрелищных моментов и интервью с победителями...',
          'status' => NewsFeed::STATUS_ARCHIVED,
          'published_at' => Carbon::now()->subYear(),
          'archived_at' => Carbon::now()->subMonth(),
          'user_id' => 1,
          'category_id' => 1,
          'is_featured' => false,
          'views_count'=>0,
          'image' => 'tournament_images/2.jpg',
          'meta_title' => null,
          'meta_description' => null,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now(),
        ],
      ]);
    }
}
