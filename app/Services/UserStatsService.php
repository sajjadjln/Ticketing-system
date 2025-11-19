<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\User;

class UserStatsService
{
    public function get(User $user): array
    {
        $mine = Ticket::createdBy($user->id);

        return [
            'my_tickets'         => $mine->count(),
            'my_open_tickets'    => (clone $mine)->openOrInProgress()->count(),
            'my_resolved_tickets'=> (clone $mine)->resolvedOrClosed()->count(),
        ];
    }
}
