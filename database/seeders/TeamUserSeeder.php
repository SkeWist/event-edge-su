<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TeamUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $teamUsers = [
            ['team_id' => 1, 'user_id' => 1], // User 1 в Team Alpha
            ['team_id' => 1, 'user_id' => 2], // User 2 в Team Alpha
            ['team_id' => 2, 'user_id' => 2], // User 2 в Team Bravo
            ['team_id' => 2, 'user_id' => 3], // User 3 в Team Bravo
            ['team_id' => 3, 'user_id' => 1], // User 1 в Team Charlie
            ['team_id' => 4, 'user_id' => 3], // User 3 в Team Delta
            ['team_id' => 4, 'user_id' => 4], // User 4 в Team Delta
        ];

        DB::table('team_user')->insert($teamUsers);
    }
}
