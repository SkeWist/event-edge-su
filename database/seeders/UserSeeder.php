<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        $users = [
            // Administrators (2)
            [
                'name' => 'User 1',
                'email' => 'admin1@example.com',
                'role_id' => 1, // Admin
                'password' => Hash::make('password123'),
                'api_token' => Str::random(60),
            ],
            [
                'name' => 'User 2',
                'email' => 'admin2@example.com',
                'role_id' => 1, // Admin
                'password' => Hash::make('password123'),
                'api_token' => Str::random(60),
            ],
            // Editor (1)
            [
                'name' => 'User 3',
                'email' => 'editor@example.com',
                'role_id' => 2, // Editor
                'password' => Hash::make('password123'),
                'api_token' => Str::random(60),
            ],
            // Operators (2)
            [
                'name' => 'User 4',
                'email' => 'operator1@example.com',
                'role_id' => 3, // Operator
                'password' => Hash::make('password123'),
                'api_token' => Str::random(60),
            ],
            [
                'name' => 'User 5',
                'email' => 'operator2@example.com',
                'role_id' => 3, // Operator
                'password' => Hash::make('password123'),
                'api_token' => Str::random(60),
            ],
            // Regular Users (31)
            [
                'name' => 'User 6',
                'email' => 'user6@example.com',
                'role_id' => 4, // User
                'password' => Hash::make('password123'),
                'api_token' => Str::random(60),
            ],
            [
                'name' => 'User 7',
                'email' => 'user7@example.com',
                'role_id' => 4,
                'password' => Hash::make('password123'),
                'api_token' => Str::random(60),
            ],
            [
                'name' => 'User 8',
                'email' => 'user8@example.com',
                'role_id' => 4,
                'password' => Hash::make('password123'),
                'api_token' => Str::random(60),
            ],
            [
                'name' => 'User 9',
                'email' => 'user9@example.com',
                'role_id' => 4,
                'password' => Hash::make('password123'),
                'api_token' => Str::random(60),
            ],
            [
                'name' => 'User 10',
                'email' => 'user10@example.com',
                'role_id' => 4,
                'password' => Hash::make('password123'),
                'api_token' => Str::random(60),
            ],
            [
                'name' => 'User 11',
                'email' => 'user11@example.com',
                'role_id' => 4,
                'password' => Hash::make('password123'),
                'api_token' => Str::random(60),
            ],
            [
                'name' => 'User 12',
                'email' => 'user12@example.com',
                'role_id' => 4,
                'password' => Hash::make('password123'),
                'api_token' => Str::random(60),
            ],
            [
                'name' => 'User 13',
                'email' => 'user13@example.com',
                'role_id' => 4,
                'password' => Hash::make('password123'),
                'api_token' => Str::random(60),
            ],
            [
                'name' => 'User 14',
                'email' => 'user14@example.com',
                'role_id' => 4,
                'password' => Hash::make('password123'),
                'api_token' => Str::random(60),
            ],
            [
                'name' => 'User 15',
                'email' => 'user15@example.com',
                'role_id' => 4,
                'password' => Hash::make('password123'),
                'api_token' => Str::random(60),
            ],
            [
                'name' => 'User 16',
                'email' => 'user16@example.com',
                'role_id' => 4,
                'password' => Hash::make('password123'),
                'api_token' => Str::random(60),
            ],
            [
                'name' => 'User 17',
                'email' => 'user17@example.com',
                'role_id' => 4,
                'password' => Hash::make('password123'),
                'api_token' => Str::random(60),
            ],
            [
                'name' => 'User 18',
                'email' => 'user18@example.com',
                'role_id' => 4,
                'password' => Hash::make('password123'),
                'api_token' => Str::random(60),
            ],
            [
                'name' => 'User 19',
                'email' => 'user19@example.com',
                'role_id' => 4,
                'password' => Hash::make('password123'),
                'api_token' => Str::random(60),
            ],
            [
                'name' => 'User 20',
                'email' => 'user20@example.com',
                'role_id' => 4,
                'password' => Hash::make('password123'),
                'api_token' => Str::random(60),
            ],
            [
                'name' => 'User 21',
                'email' => 'user21@example.com',
                'role_id' => 4,
                'password' => Hash::make('password123'),
                'api_token' => Str::random(60),
            ],
            [
                'name' => 'User 22',
                'email' => 'user22@example.com',
                'role_id' => 4,
                'password' => Hash::make('password123'),
                'api_token' => Str::random(60),
            ],
            [
                'name' => 'User 23',
                'email' => 'user23@example.com',
                'role_id' => 4,
                'password' => Hash::make('password123'),
                'api_token' => Str::random(60),
            ],
            [
                'name' => 'User 24',
                'email' => 'user24@example.com',
                'role_id' => 4,
                'password' => Hash::make('password123'),
                'api_token' => Str::random(60),
            ],
            [
                'name' => 'User 25',
                'email' => 'user25@example.com',
                'role_id' => 4,
                'password' => Hash::make('password123'),
                'api_token' => Str::random(60),
            ],
            [
                'name' => 'User 26',
                'email' => 'user26@example.com',
                'role_id' => 4,
                'password' => Hash::make('password123'),
                'api_token' => Str::random(60),
            ],
            [
                'name' => 'User 27',
                'email' => 'user27@example.com',
                'role_id' => 4,
                'password' => Hash::make('password123'),
                'api_token' => Str::random(60),
            ],
            [
                'name' => 'User 28',
                'email' => 'user28@example.com',
                'role_id' => 4,
                'password' => Hash::make('password123'),
                'api_token' => Str::random(60),
            ],
            [
                'name' => 'User 29',
                'email' => 'user29@example.com',
                'role_id' => 4,
                'password' => Hash::make('password123'),
                'api_token' => Str::random(60),
            ],
            [
                'name' => 'User 30',
                'email' => 'user30@example.com',
                'role_id' => 4,
                'password' => Hash::make('password123'),
                'api_token' => Str::random(60),
            ],
            [
                'name' => 'User 31',
                'email' => 'user31@example.com',
                'role_id' => 4,
                'password' => Hash::make('password123'),
                'api_token' => Str::random(60),
            ],
            [
                'name' => 'User 32',
                'email' => 'user32@example.com',
                'role_id' => 4,
                'password' => Hash::make('password123'),
                'api_token' => Str::random(60),
            ],
            [
                'name' => 'User 33',
                'email' => 'user33@example.com',
                'role_id' => 4,
                'password' => Hash::make('password123'),
                'api_token' => Str::random(60),
            ],
            [
                'name' => 'User 34',
                'email' => 'user34@example.com',
                'role_id' => 4,
                'password' => Hash::make('password123'),
                'api_token' => Str::random(60),
            ],
            [
                'name' => 'User 35',
                'email' => 'user35@example.com',
                'role_id' => 4,
                'password' => Hash::make('password123'),
                'api_token' => Str::random(60),
            ],
            [
                'name' => 'User 36',
                'email' => 'user36@example.com',
                'role_id' => 4,
                'password' => Hash::make('password123'),
                'api_token' => Str::random(60),
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
