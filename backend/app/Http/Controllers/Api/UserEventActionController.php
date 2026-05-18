<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\UserEventAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserEventActionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'action_type' => 'required|in:attending,interested,saved',
        ]);

        $action = UserEventAction::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'event_id' => $validated['event_id'],
                'action_type' => $validated['action_type'],
            ],
            []
        );

        $event = Event::find($validated['event_id']);

        if ($validated['action_type'] === 'attending') {
            $event->increment('attending_count');
        } elseif ($validated['action_type'] === 'saved') {
            $event->increment('save_count');
        }

        return response()->json($action, 201);
    }

    public function destroy(Request $request, Event $event, string $actionType): JsonResponse
    {
        if (!in_array($actionType, ['attending', 'interested', 'saved'])) {
            return response()->json(['message' => 'Invalid action type'], 422);
        }

        UserEventAction::where('user_id', $request->user()->id)
            ->where('event_id', $event->id)
            ->where('action_type', $actionType)
            ->delete();

        return response()->json(['message' => 'Action removed']);
    }

    public function myActions(Request $request): JsonResponse
    {
        $actions = $request->user()
            ->eventActions()
            ->with('event')
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($actions);
    }
}
