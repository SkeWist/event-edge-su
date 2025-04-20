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
            // Team Alpha (Team 1) - Active
            ['team_id' => 1, 'user_id' => 1, 'status' => 'active'],   // Admin 1 (капитан)
            ['team_id' => 1, 'user_id' => 18, 'status' => 'active'],  // User 18
            ['team_id' => 1, 'user_id' => 19, 'status' => 'active'],  // User 19

            // Team Bravo (Team 2) - Active
            ['team_id' => 2, 'user_id' => 2, 'status' => 'active'],   // Admin 2 (капитан)
            ['team_id' => 2, 'user_id' => 20, 'status' => 'active'],  // User 20
            ['team_id' => 2, 'user_id' => 21, 'status' => 'active'],  // User 21

            // Team Charlie (Team 3) - Active
            ['team_id' => 3, 'user_id' => 3, 'status' => 'active'],   // Editor (капитан)
            ['team_id' => 3, 'user_id' => 22, 'status' => 'active'],  // User 22
            ['team_id' => 3, 'user_id' => 23, 'status' => 'active'],  // User 23

            // Team Delta (Team 4) - Active
            ['team_id' => 4, 'user_id' => 4, 'status' => 'active'],   // Operator 1 (капитан)
            ['team_id' => 4, 'user_id' => 24, 'status' => 'active'],  // User 24
            ['team_id' => 4, 'user_id' => 25, 'status' => 'active'],  // User 25

            // Team Echo (Team 5) - Active
            ['team_id' => 5, 'user_id' => 5, 'status' => 'active'],   // Operator 2 (капитан)
            ['team_id' => 5, 'user_id' => 26, 'status' => 'active'],  // User 26
            ['team_id' => 5, 'user_id' => 27, 'status' => 'active'],  // User 27

            // Team Foxtrot (Team 6) - Active
            ['team_id' => 6, 'user_id' => 6, 'status' => 'active'],   // User 6 (капитан)
            ['team_id' => 6, 'user_id' => 28, 'status' => 'active'],  // User 28
            ['team_id' => 6, 'user_id' => 29, 'status' => 'active'],  // User 29

            // Team Golf (Team 7) - Active
            ['team_id' => 7, 'user_id' => 7, 'status' => 'active'],   // User 7 (капитан)
            ['team_id' => 7, 'user_id' => 30, 'status' => 'active'],  // User 30
            ['team_id' => 7, 'user_id' => 31, 'status' => 'active'],  // User 31

            // Team Hotel (Team 8) - Inactive
            ['team_id' => 8, 'user_id' => 8, 'status' => 'inactive'], // User 8 (капитан)
            ['team_id' => 8, 'user_id' => 32, 'status' => 'inactive'], // User 32
            ['team_id' => 8, 'user_id' => 33, 'status' => 'inactive'], // User 33

            // Team India (Team 9) - Active
            ['team_id' => 9, 'user_id' => 9, 'status' => 'active'],   // User 9 (капитан)
            ['team_id' => 9, 'user_id' => 34, 'status' => 'active'],  // User 34
            ['team_id' => 9, 'user_id' => 35, 'status' => 'active'],  // User 35

            // Team Juliet (Team 10) - Active
            ['team_id' => 10, 'user_id' => 10, 'status' => 'active'], // User 10 (капитан)
            ['team_id' => 10, 'user_id' => 36, 'status' => 'active'], // User 36
            ['team_id' => 10, 'user_id' => 13, 'status' => 'active'], // User 13

            // Team Kilo (Team 11) - Inactive
            ['team_id' => 11, 'user_id' => 11, 'status' => 'inactive'], // User 11 (капитан)
            ['team_id' => 11, 'user_id' => 14, 'status' => 'inactive'], // User 14
            ['team_id' => 11, 'user_id' => 15, 'status' => 'inactive'], // User 15

            // Team Lima (Team 12) - Active
            ['team_id' => 12, 'user_id' => 12, 'status' => 'active'], // User 12 (капитан)
            ['team_id' => 12, 'user_id' => 16, 'status' => 'active'], // User 16
            ['team_id' => 12, 'user_id' => 17, 'status' => 'active'], // User 17
        ];

        DB::table('team_user')->insert($teamUsers);
    }
}
