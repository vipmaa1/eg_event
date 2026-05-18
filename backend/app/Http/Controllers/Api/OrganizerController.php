<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganizerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);
    }

    public function index(Request $request): JsonResponse
    {
        $organizers = Organizer::active()
            ->withCount('followers')
            ->orderByDesc('verified')
            ->paginate(20);

        return response()->json($organizers);
    }

    public function show(Organizer $organizer): JsonResponse
    {
        $organizer->load(['events' => function ($q) {
            $q->published()->upcoming()->with('venue');
        }]);

        return response()->json($organizer);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'bio_en' => 'nullable|string',
            'bio_ar' => 'nullable|string',
            'logo' => 'nullable|string|max:500',
            'cover_image' => 'nullable|string|max:500',
            'website' => 'nullable|string|max:500',
            'email' => 'nullable|string|email|max:255',
            'phone' => 'nullable|string|max:20',
            'social_media' => 'nullable|array',
        ]);

        $validated['user_id'] = $request->user()->id;

        $organizer = Organizer::create($validated);
        $request->user()->assignRole('organizer');

        return response()->json($organizer, 201);
    }

    public function update(Request $request, Organizer $organizer): JsonResponse
    {
        if ($organizer->user_id !== $request->user()->id && !$request->user()->hasRole('admin')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'name_en' => 'sometimes|string|max:255',
            'name_ar' => 'sometimes|nullable|string|max:255',
            'bio_en' => 'sometimes|nullable|string',
            'bio_ar' => 'sometimes|nullable|string',
            'logo' => 'sometimes|nullable|string|max:500',
            'cover_image' => 'sometimes|nullable|string|max:500',
            'website' => 'sometimes|nullable|string|max:500',
            'email' => 'sometimes|nullable|string|email',
            'phone' => 'sometimes|nullable|string|max:20',
            'social_media' => 'sometimes|nullable|array',
        ]);

        $organizer->update($validated);

        return response()->json($organizer);
    }

    public function dashboard(Request $request): JsonResponse
    {
        $organizer = $request->user()->organizer;

        if (!$organizer) {
            return response()->json(['message' => 'Not an organizer'], 403);
        }

        $events = $organizer->events()
            ->with(['ticketTypes', 'venue:id,name_en,name_ar,city'])
            ->withCount('tickets')
            ->orderByDesc('created_at')
            ->get();

        $totalTicketsSold = $events->sum('tickets_count');
        $totalRevenue = $organizer->payouts()->where('status', 'paid')->sum('net_amount');
        $pendingEvents = $organizer->events()->where('status', 'pending_approval')->count();

        $events->each(function ($event) {
            $event->loadCount(['tickets as checked_in_count' => fn($q) => $q->where('checked_in', true)]);
        });

        return response()->json([
            'organizer' => $organizer,
            'total_events' => $events->count(),
            'events' => $events,
            'total_tickets_sold' => $totalTicketsSold,
            'total_revenue' => $totalRevenue,
            'pending_events' => $pendingEvents,
        ]);
    }
}
