<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Events\EventPublished;
use App\Services\SystemConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);
    }

    public function index(Request $request): JsonResponse
    {
        $query = Event::published()->with(['organizer', 'venue:id,name_en,name_ar,city']);

        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('city')) {
            $query->whereHas('venue', fn($q) => $q->where('city', $request->city));
        }

        if ($request->boolean('upcoming')) {
            $query->upcoming();
        }

        if ($request->filled('date_from')) {
            $query->where('start_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('end_date', '<=', $request->date_to);
        }

        if ($request->boolean('free')) {
            $query->where('is_free', true);
        }

        $sort = $request->sort ?? 'start_date';
        $order = $request->order ?? 'asc';
        $query->orderBy($sort, $order);

        $events = $query->paginate(20);

        return response()->json($events);
    }

    public function show(Event $event): JsonResponse
    {
        if ($event->status !== 'published' && (!auth()->check() || auth()->id() !== $event->created_by_user_id)) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $event->load([
            'organizer',
            'venue',
            'ticketTypes' => fn($q) => $q->where('status', 'available'),
            'comments' => fn($q) => $q->whereNull('parent_id')->where('is_approved', true)->with('user', 'replies.user'),
        ]);

        $event->incrementViewCount();

        return response()->json($event);
    }

    public function store(Request $request): JsonResponse
    {
        $organizer = $request->user()->organizer;

        if (!$organizer) {
            return response()->json(['message' => 'You must be an organizer to create events'], 403);
        }

        $validated = $request->validate([
            'title_en' => 'required|string|max:255',
            'title_ar' => 'nullable|string|max:255',
            'description_en' => 'required|string',
            'description_ar' => 'nullable|string',
            'start_date' => 'required|date|after:now',
            'end_date' => 'required|date|after:start_date',
            'category' => 'required|in:concerts,sports,business,cultural,food,arts,family,nightlife,other',
            'cover_image' => 'nullable|string|max:500',
            'gallery' => 'nullable|array',
            'tags' => 'nullable|array',
            'venue_id' => 'nullable|exists:venues,id',
            'is_free' => 'boolean',
        ]);

        $validated['organizer_id'] = $organizer->id;
        $validated['created_by_user_id'] = $request->user()->id;
        $validated['status'] = 'draft';

        $event = Event::create($validated);

        return response()->json($event, 201);
    }

    public function update(Request $request, Event $event): JsonResponse
    {
        if ($event->created_by_user_id !== $request->user()->id && !$request->user()->hasRole('admin')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'title_en' => 'sometimes|string|max:255',
            'title_ar' => 'sometimes|nullable|string|max:255',
            'description_en' => 'sometimes|string',
            'description_ar' => 'sometimes|nullable|string',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'category' => 'sometimes|in:concerts,sports,business,cultural,food,arts,family,nightlife,other',
            'cover_image' => 'sometimes|nullable|string|max:500',
            'gallery' => 'sometimes|nullable|array',
            'tags' => 'sometimes|nullable|array',
            'venue_id' => 'sometimes|nullable|exists:venues,id',
            'is_free' => 'sometimes|boolean',
        ]);

        $event->update($validated);

        return response()->json($event);
    }

    public function publish(Request $request, Event $event): JsonResponse
    {
        if ($event->created_by_user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $autoApprove = SystemConfig::get('auto_approve_events', true);

        if ($autoApprove) {
            $event->update(['status' => 'published']);
            event(new EventPublished($event));
            return response()->json(['message' => 'Event published', 'event' => $event]);
        }

        $event->update(['status' => 'pending_approval']);

        logger("Event [{$event->id}] submitted for approval by organizer [{$request->user()->id}]");

        return response()->json(['message' => 'Event submitted for approval', 'event' => $event]);
    }

    public function cancel(Request $request, Event $event): JsonResponse
    {
        if ($event->created_by_user_id !== $request->user()->id && !$request->user()->hasRole('admin')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $event->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Event cancelled']);
    }

    public function myEvents(Request $request): JsonResponse
    {
        $organizer = $request->user()->organizer;

        if (!$organizer) {
            return response()->json(['message' => 'Not an organizer'], 403);
        }

        $events = $organizer->events()
            ->with(['ticketTypes', 'venue'])
            ->withCount('tickets')
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($events);
    }
}
