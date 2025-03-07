<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->insert([
            [
                'id' => 1,
                'name' => 'User 1',
                'email' => 'user1@example.com',
                'role_id' => 1,
                'password' => Hash::make('password123'),
                'remember_token' => Str::random(10),
            ],
            [
                'id' => 2,
                'name' => 'User 2',
                'email' => 'user2@example.com',
                'role_id' => 2,
                'password' => Hash::make('password123'),
                'remember_token' => Str::random(10),
            ],
            [
                'id' => 3,
                'name' => 'User 3',
                'email' => 'user3@example.com',
                'role_id' => 1,
                'password' => Hash::make('password123'),
                'remember_token' => Str::random(10),
            ],
            [
                'id' => 4,
                'name' => 'User 4',
                'email' => 'user4@example.com',
                'role_id' => 1,
                'password' => Hash::make('password123'),
                'remember_token' => Str::random(10),
            ],
        ]);
    }
}
