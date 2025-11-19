<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Notifications\TicketCreatedNotification;
use App\Http\Requests\StoreTicketWithAttachmentsRequest;
use App\Models\Attachment;
class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            $tickets = Ticket::with(['user', 'assignee']);
        } elseif ($user->isAgent()) {
            $tickets = Ticket::with(['user', 'assignee'])
                ->where(function ($query) use ($user) {
                    $query->where('assigned_to', $user->id)
                        ->orWhereNull('assigned_to');
                });
        } else {
            $tickets = Ticket::with(['user', 'assignee'])
                ->where('user_id', $user->id);
        }

        if ($request->has('status')) {
            $tickets->where('status', $request->status);
        }

        if ($request->has('priority')) {
            $tickets->where('priority', $request->priority);
        }

        if ($request->has('category')) {
            $tickets->where('category', $request->category);
        }

        $tickets = $tickets->latest()->paginate(10);

        return response()->json($tickets);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTicketWithAttachmentsRequest $request)
    {

        $ticket = Ticket::create([
            'user_id' => $request->user()->id,
            'title' => $request->title,
            'description' => $request->description,
            'category' => $request->category,
            'priority' => $request->priority,
            'status' => 'open',
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('attachments/' . date('Y/m'));

                Attachment::create([
                    'filename' => $file->getClientOriginalName(),
                    'path' => $path,
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'ticket_id' => $ticket->id,
                    'user_id' => $request->user()->id,
                ]);
            }
        }

        $adminsAndAgents = User::whereIn('role', ['admin', 'agent'])->get();
        foreach ($adminsAndAgents as $user) {
            $user->notify(new TicketCreatedNotification($ticket));
        }

        return response()->json($ticket->load('user'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Ticket $ticket)
    {
        $user = request()->user();

        if ($user->isUser() && $ticket->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($user->isAgent() && $ticket->assigned_to !== $user->id && !is_null($ticket->assigned_to)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($ticket->load(['user', 'assignee', 'comments.user']));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ticket $ticket)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ticket $ticket)
    {
        $user = $request->user();

        if ($user->isUser() && $ticket->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validationRules = [];

        if ($user->isUser()) {
            $validationRules = [
                'title' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
            ];

            $request->merge([
                'status' => $ticket->status,
                'priority' => $ticket->priority,
                'assigned_to' => $ticket->assigned_to,
            ]);
        } else {
            $validationRules = [
                'title' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'status' => 'sometimes|in:open,in_progress,resolved,closed',
                'priority' => 'sometimes|in:low,medium,high',
                'assigned_to' => 'sometimes|exists:users,id',
                'category' => 'sometimes|in:technical,billing,general,other',
            ];
        }

        $request->validate($validationRules);

        if ($request->has('status') && $user->isAgent()) {
            if (!$ticket->isValidStatusTransition($request->status)) {
                return response()->json([
                    'error' => 'Invalid status transition'
                ], 422);
            }
        }

        $ticket->update($request->all());

        return response()->json($ticket->load(['user', 'assignee']));
    }

    public function search(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Ticket::with(['user', 'assignee']);

        if ($user->isUser()) {
            $query->where('user_id', $user->id);
        } elseif ($user->isAgent()) {
            $query->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                    ->orWhereNull('assigned_to');
            });
        }

        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        if ($request->has('statuses')) {
            $query->whereIn('status', explode(',', $request->statuses));
        }

        if ($request->has('priorities')) {
            $query->whereIn('priority', explode(',', $request->priorities));
        }

        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $tickets = $query->latest()->paginate($request->per_page ?? 15);

        return response()->json($tickets);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ticket $ticket)
    {
        $user = request()->user();

        if (!$user->isAdmin() && $ticket->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $ticket->delete();

        return response()->json(['message' => 'Ticket deleted successfully']);
    }
}
