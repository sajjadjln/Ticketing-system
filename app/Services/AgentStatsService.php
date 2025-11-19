<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\User;

class AgentStatsService
{
    public function get(User $user): array
    {
        $assigned = Ticket::assignedTo($user->id);

        return [
            'my_assigned_tickets' => $assigned->count(),
            'my_open_tickets'     => (clone $assigned)->openOrInProgress()->count(),
            'my_resolved_tickets' => (clone $assigned)->resolved()->count(),
            'unassigned_tickets'  => Ticket::unassigned()->open()->count(),
        ];
    }
}
