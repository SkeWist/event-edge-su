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
            [
                'name' => 'User 1',
                'email' => 'user1@example.com',
                'role_id' => 1,
                'password' => Hash::make('password123'),
                'api_token' => Str::random(60), // Генерация токена
            ],
            [
                'name' => 'User 2',
                'email' => 'user2@example.com',
                'role_id' => 2,
                'password' => Hash::make('password123'),
                'api_token' => Str::random(60),
            ],
            [
                'name' => 'User 3',
                'email' => 'user3@example.com',
                'role_id' => 1,
                'password' => Hash::make('password123'),
                'api_token' => Str::random(60),
            ],
            [
                'name' => 'User 4',
                'email' => 'user4@example.com',
                'role_id' => 1,
                'password' => Hash::make('password123'),
                'api_token' => Str::random(60),
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
