<?php

namespace Database\Seeders;

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
        // Очистка таблицы перед заполнением (опционально)
        DB::table('news_feeds')->truncate();

        // Данные для заполнения
        $newsFeeds = [
            [
                'title' => 'Шок, валорант оказался провальным проектом',
                'description' => 'Такая плохая игра, просто мерзость',
                'status' => 'published',
                'published_at' => Carbon::now(),
                'user_id' => 1, // ID пользователя, который опубликовал новость
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Ого, лига стала лучше',
                'description' => 'Никто в это не верит',
                'status' => 'draft',
                'published_at' => null, // Новость еще не опубликована
                'user_id' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];
        // Вставка данных в таблицу
        DB::table('news_feeds')->insert($newsFeeds);
    }
}
