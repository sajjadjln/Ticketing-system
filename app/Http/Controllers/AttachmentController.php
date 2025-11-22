<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Ticket;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AttachmentController extends Controller
{
    public function store(Request $request, Ticket $ticket): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:10240',
            'comment_id' => 'nullable|exists:comments,id'
        ]);

        $user = $request->user();
        
        if ($user->isUser() && $ticket->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($user->isAgent() && $ticket->assigned_to !== $user->id && !is_null($ticket->assigned_to)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $file = $request->file('file');
        
        $allowedMimes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain',
            'application/zip',
            'application/x-rar-compressed'
        ];

        if (!in_array($file->getMimeType(), $allowedMimes)) {
            return response()->json(['error' => 'File type not allowed'], 422);
        }

        $path = $file->store('attachments/' . date('Y/m'));

        $attachment = Attachment::create([
            'filename' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'ticket_id' => $ticket->id,
            'comment_id' => $request->comment_id,
            'user_id' => $user->id,
        ]);

        return response()->json($attachment->load('user'), 201);
    }

    public function destroy(Attachment $attachment): JsonResponse
    {
        $user = request()->user();
        
        if (!$user->isAdmin() && $attachment->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        Storage::delete($attachment->path);

        $attachment->delete();

        return response()->json(['message' => 'Attachment deleted successfully']);
    }

    public function download(Attachment $attachment): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $user = request()->user();
        $ticket = $attachment->ticket;
        
        if ($user->isUser() && $ticket->user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        if ($user->isAgent() && $ticket->assigned_to !== $user->id && !is_null($ticket->assigned_to)) {
            abort(403, 'Unauthorized');
        }

        if (!Storage::exists($attachment->path)) {
            abort(404, 'File not found');
        }

        return response()->download(
            storage_path('app/' . $attachment->path),
            $attachment->filename
        );
    }

    public function index(Ticket $ticket): JsonResponse
    {
        $user = request()->user();
        
        if ($user->isUser() && $ticket->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($user->isAgent() && $ticket->assigned_to !== $user->id && !is_null($ticket->assigned_to)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $attachments = $ticket->attachments()
            ->with('user')
            ->latest()
            ->get();

        return response()->json($attachments);
    }
}