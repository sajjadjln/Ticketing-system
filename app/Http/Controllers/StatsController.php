<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class StatsController extends Controller
{
    public function dashboard(): JsonResponse
    {
        $user = request()->user();
        
        if ($user->isAdmin()) {
            $stats = $this->adminStats();
        } elseif ($user->isAgent()) {
            $stats = $this->agentStats($user);
        } else {
            $stats = $this->userStats($user);
        }

        return response()->json($stats);
    }

    private function adminStats(): array
    {
        return [
            'total_tickets' => Ticket::count(),
            'open_tickets' => Ticket::where('status', 'open')->count(),
            'in_progress_tickets' => Ticket::where('status', 'in_progress')->count(),
            'resolved_tickets' => Ticket::where('status', 'resolved')->count(),
            'closed_tickets' => Ticket::where('status', 'closed')->count(),
            'unassigned_tickets' => Ticket::whereNull('assigned_to')->where('status', 'open')->count(),
            'tickets_by_priority' => Ticket::groupBy('priority')
                ->selectRaw('priority, count(*) as count')
                ->get(),
            'tickets_by_category' => Ticket::groupBy('category')
                ->selectRaw('category, count(*) as count')
                ->get(),
        ];
    }

    private function agentStats(User $user): array
    {
        return [
            'my_assigned_tickets' => Ticket::where('assigned_to', $user->id)->count(),
            'my_open_tickets' => Ticket::where('assigned_to', $user->id)
                ->whereIn('status', ['open', 'in_progress'])
                ->count(),
            'my_resolved_tickets' => Ticket::where('assigned_to', $user->id)
                ->where('status', 'resolved')
                ->count(),
            'unassigned_tickets' => Ticket::whereNull('assigned_to')
                ->where('status', 'open')
                ->count(),
        ];
    }

    private function userStats(User $user): array
    {
        return [
            'my_tickets' => Ticket::where('user_id', $user->id)->count(),
            'my_open_tickets' => Ticket::where('user_id', $user->id)
                ->whereIn('status', ['open', 'in_progress'])
                ->count(),
            'my_resolved_tickets' => Ticket::where('user_id', $user->id)
                ->whereIn('status', ['resolved', 'closed'])
                ->count(),
        ];
    }
}