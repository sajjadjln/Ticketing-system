<?php

namespace App\Services;

use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

class AdminStatsService
{
    public function get(): array
    {
        $statusCounts = Ticket::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'total_tickets'         => Ticket::count(),
            'open_tickets'          => $statusCounts[Ticket::STATUS_OPEN] ?? 0,
            'in_progress_tickets'   => $statusCounts[Ticket::STATUS_IN_PROGRESS] ?? 0,
            'resolved_tickets'      => $statusCounts[Ticket::STATUS_RESOLVED] ?? 0,
            'closed_tickets'        => $statusCounts[Ticket::STATUS_CLOSED] ?? 0,
            'unassigned_tickets'    => Ticket::unassigned()->open()->count(),

            'tickets_by_priority'   => $this->groupBy('priority'),
            'tickets_by_category'   => $this->groupBy('category'),
        ];
    }

    private function groupBy(string $column)
    {
        return Ticket::select($column, DB::raw('COUNT(*) as total'))
            ->groupBy($column)
            ->get();
    }
}
