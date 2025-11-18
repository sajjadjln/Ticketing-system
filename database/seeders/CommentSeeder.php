<?php
// database/seeders/CommentSeeder.php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    public function run(): void
    {
        $tickets = Ticket::all();
        $users = User::all();
        $agents = User::where('role', 'agent')->get();

        foreach ($tickets as $ticket) {
            $commentCount = rand(1, 5);
            
            for ($i = 0; $i < $commentCount; $i++) {
                if ($i === 0) {
                    $userId = $ticket->user_id;
                } else {
                    $userId = rand(0, 1) ? $agents->random()->id : $ticket->user_id;
                }

                Comment::factory()->create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $userId,
                ]);
            }
        }
    }
}