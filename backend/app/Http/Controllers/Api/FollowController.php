<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Follow;
use App\Models\Organizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function follow(Request $request, Organizer $organizer): JsonResponse
    {
        if ($organizer->user_id === $request->user()->id) {
            return response()->json(['message' => 'Cannot follow yourself'], 422);
        }

        $follow = Follow::firstOrCreate([
            'user_id' => $request->user()->id,
            'organizer_id' => $organizer->id,
        ]);

        return response()->json(['message' => 'Followed', 'follow' => $follow]);
    }

    public function unfollow(Request $request, Organizer $organizer): JsonResponse
    {
        Follow::where('user_id', $request->user()->id)
            ->where('organizer_id', $organizer->id)
            ->delete();

        return response()->json(['message' => 'Unfollowed']);
    }

    public function myFollows(Request $request): JsonResponse
    {
        $organizers = $request->user()
            ->follows()
            ->with('organizer')
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($organizers);
    }
}
