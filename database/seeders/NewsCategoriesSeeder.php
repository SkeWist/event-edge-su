<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class NewsCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Очистка таблицы перед заполнением (опционально)
        DB::table('news_categories')->insert([


            ['name' => 'Турниры',
              'is_active' => true,
              'slug' => 'tournament' ,
              'created_at' => now(),
              'updated_at' => now(),],
            ['name' => 'Киберспорт',
              'is_active' => true,
              'slug' => 'esport',
              'created_at' => now(),
              'updated_at' => now(),
              ],
            ['name' => 'Игровые события',
              'is_active' => true,
              'slug' => 'esport-event',
              'created_at' => now(),
              'updated_at' => now(),
            ],
        ]);

    }
}
