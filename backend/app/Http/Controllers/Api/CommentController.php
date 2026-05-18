<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index']);
    }

    public function index(int $eventId): JsonResponse
    {
        $comments = Comment::with(['user:id,name,profile_photo', 'replies.user:id,name,profile_photo'])
            ->where('event_id', $eventId)
            ->whereNull('parent_id')
            ->where('is_approved', true)
            ->orderByDesc('created_at')
            ->paginate(15);

        return response()->json($comments);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'parent_id' => 'nullable|exists:comments,id',
            'content' => 'required|string|min:1|max:2000',
        ]);

        $comment = Comment::create([
            'user_id' => $request->user()->id,
            'event_id' => $validated['event_id'],
            'parent_id' => $validated['parent_id'] ?? null,
            'content' => $validated['content'],
        ]);

        $comment->load('user:id,name,profile_photo');

        return response()->json($comment, 201);
    }

    public function destroy(Request $request, Comment $comment): JsonResponse
    {
        if ($comment->user_id !== $request->user()->id && !$request->user()->hasRole('admin')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'Comment deleted']);
    }
}
