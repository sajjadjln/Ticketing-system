<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@tickets.com',
            'role' => 'admin',
        ]);

        User::factory()->create(([
            'name' => 'Agent User',
            'email' => 'agent@tickets.com',
            'role' => 'agent'
        ]));

        User::factory()->count(3)->agent()->create([]);

        User::factory()->count(10)->user()->create();
    }
}
