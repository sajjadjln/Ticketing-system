<?php
// database/seeders/TicketSeeder.php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('role', 'user')->get();
        $agents = User::where('role', 'agent')->get();

        Ticket::factory()->count(10)->create();
        
        Ticket::factory()->count(5)->assigned()->create([
            'assigned_to' => $agents->random()->id,
        ]);

        Ticket::factory()->count(15)->assigned()->create([
            'assigned_to' => $agents->random()->id,
            'status' => 'in_progress',
        ]);

        Ticket::factory()->count(10)->resolved()->create([
            'assigned_to' => $agents->random()->id,
        ]);

        Ticket::factory()->count(5)->closed()->create([
            'assigned_to' => $agents->random()->id,
        ]);

        Ticket::factory()->count(3)->highPriority()->create();
        Ticket::factory()->count(2)->highPriority()->assigned()->create([
            'assigned_to' => $agents->random()->id,
        ]);
    }
}