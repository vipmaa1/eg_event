<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\CheckInService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckInController extends Controller
{
    public function __construct(
        private CheckInService $checkInService,
    ) {}

    public function scan(Request $request): JsonResponse
    {
        $organizer = $request->user()->organizer;

        if (!$organizer) {
            return response()->json(['message' => 'Only organizers can scan tickets'], 403);
        }

        $validated = $request->validate([
            'ticket_code' => 'required|string',
            'event_id' => 'required|exists:events,id',
        ]);

        $event = Event::findOrFail($validated['event_id']);

        if ($event->organizer_id !== $organizer->id) {
            return response()->json(['message' => 'You can only check in to your own events'], 403);
        }

        return response()->json($this->checkInService->scan($validated));
    }

    public function eventStats(Request $request, Event $event): JsonResponse
    {
        $organizer = $request->user()->organizer;

        if (!$organizer || $event->organizer_id !== $organizer->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json($this->checkInService->getEventCheckIns($event->id));
    }
}
