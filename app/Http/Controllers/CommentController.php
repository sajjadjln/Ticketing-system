<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Ticket;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Ticket $ticket): JsonResponse
    {
        $user = request()->user();

        if ($user->isUser() && $ticket->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($user->isAgent() && $ticket->assigned_to !== $user->id && !is_null($ticket->assigned_to)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $comments = $ticket->comments()
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($comments);
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
    public function store(Request $request, Ticket $ticket)
    {
        $request->validate([
            'comment_text' => 'required|string|max:1000',
        ]);

        $user = $request->user();

        if ($user->isUser() && $ticket->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($user->isAgent() && $ticket->assigned_to !== $user->id && !is_null($ticket->assigned_to)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($ticket->status === 'closed') {
            return response()->json(['error' => 'Cannot comment on closed tickets'], 422);
        }

        $comment = Comment::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'comment_text' => $request->comment_text,
        ]);

        return response()->json($comment->load('user'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Comment $comment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Comment $comment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ticket $ticket, Comment $comment): JsonResponse
    {
        $request->validate([
            'comment_text' => 'required|string|max:1000',
        ]);

        $user = $request->user();

        if ($comment->user_id !== $user->id) {
            return response()->json(['error' => 'Can only edit your own comments'], 403);
        }

        if ($ticket->status === 'closed') {
            return response()->json(['error' => 'Cannot update comments on closed tickets'], 422);
        }

        if ($comment->created_at->diffInHours(now()) > 1) {
            return response()->json(['error' => 'Can only edit comments within 1 hour'], 422);
        }

        $comment->update([
            'comment_text' => $request->comment_text,
        ]);

        return response()->json($comment->load('user'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comment $comment)
    {
        $user = request()->user();

        if (!$user->isAdmin() && $comment->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'Comment deleted successfully']);
    }
}
