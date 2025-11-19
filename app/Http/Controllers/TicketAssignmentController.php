<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Notifications\TicketAssignedNotification;

class TicketAssignmentController extends Controller
{
    public function assign(Request $request, Ticket $ticket): JsonResponse
    {
        $request->validate([
            'agent_id' => 'required|exists:users,id'
        ]);

        $user = $request->user();
        $agent = User::find($request->agent_id);

        if (!$user->isAgent() && !$user->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (!$agent->isAgent() && !$agent->isAdmin()) {
            return response()->json(['error' => 'Can only assign to agents or admins'], 422);
        }

        $ticket->update([
            'assigned_to' => $agent->id,
            'status' => 'in_progress'
        ]);

        $agent->notify(new TicketAssignedNotification($ticket->load('user')));

        return response()->json([
            'message' => 'Ticket assigned successfully',
            'ticket' => $ticket->load(['user', 'assignee'])
        ]);
    }

    public function autoAssign(Ticket $ticket): JsonResponse
    {
        $user = request()->user();

        if (!$user->isAgent() && !$user->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $agent = $this->findAvailableAgent();

        if (!$agent) {
            return response()->json(['error' => 'No available agents'], 422);
        }

        $ticket->update([
            'assigned_to' => $agent->id,
            'status' => 'in_progress'
        ]);

        return response()->json([
            'message' => 'Ticket auto-assigned successfully',
            'ticket' => $ticket->load(['user', 'assignee'])
        ]);
    }

    public function unassign(Ticket $ticket): JsonResponse
    {
        $user = request()->user();

        if (!$user->isAgent() && !$user->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $ticket->update([
            'assigned_to' => null,
            'status' => 'open'
        ]);

        return response()->json([
            'message' => 'Ticket unassigned successfully',
            'ticket' => $ticket->load(['user', 'assignee'])
        ]);
    }

    private function findAvailableAgent(): ?User
    {
        return User::whereIn('role', ['agent', 'admin'])
            ->withCount(['assignedTickets as active_tickets_count' => function ($query) {
                $query->whereIn('status', ['open', 'in_progress']);
            }])
            ->orderBy('active_tickets_count')
            ->first();
    }
}
