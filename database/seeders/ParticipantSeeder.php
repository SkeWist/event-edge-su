<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Team;
use App\Models\Tournament;

class ParticipantSeeder extends Seeder
{
    /**
     * Запуск сидера.
     */
    public function run()
    {
        DB::table('participants')->insert([
            [
                'user_id' => 1, // Убедитесь, что этот user_id существует в таблице users
                'team_id' => 1,
                'tournament_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2, // Убедитесь, что этот user_id существует в таблице users
                'team_id' => 2,
                'tournament_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 3, // Убедитесь, что этот user_id существует в таблице users
                'team_id' => 1,
                'tournament_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 4, // Убедитесь, что этот user_id существует в таблице users
                'team_id' => 3,
                'tournament_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
