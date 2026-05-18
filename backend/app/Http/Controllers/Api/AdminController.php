<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Organizer;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\Venue;
use App\Events\EventPublished;
use App\Services\SystemConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:admin');
    }

    public function dashboard(): JsonResponse
    {
        $totalUsers = User::count();
        $totalEvents = Event::count();
        $totalOrganizers = Organizer::count();
        $totalVenues = Venue::count();
        $pendingApproval = Event::where('status', 'pending_approval')->count();
        $pendingOrganizers = Organizer::where('verified', false)->count();
        $totalRevenue = \App\Models\Order::where('status', 'paid')->sum('total_amount');

        logger("Admin dashboard viewed");

        return response()->json([
            'total_users' => $totalUsers,
            'total_events' => $totalEvents,
            'total_organizers' => $totalOrganizers,
            'total_venues' => $totalVenues,
            'pending_approval' => $pendingApproval,
            'pending_organizers' => $pendingOrganizers,
            'total_revenue' => $totalRevenue,
        ]);
    }

    // ─── Events ────────────────────────────────────────────────────────

    public function listEvents(Request $request): JsonResponse
    {
        $query = Event::with(['organizer', 'venue']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title_en', 'like', "%{$request->search}%")
                  ->orWhere('title_ar', 'like', "%{$request->search}%");
            });
        }

        $events = $query->orderByDesc('created_at')->paginate(20);

        return response()->json($events);
    }

    public function deleteEvent(Event $event): JsonResponse
    {
        logger("Event [{$event->id}] deleted by admin [{$event->title_en}]");

        $event->delete();

        return response()->json(['message' => 'Event deleted']);
    }

    // ─── Pending (moved from separate methods) ─────────────────────────

    public function listPendingEvents(): JsonResponse
    {
        $events = Event::where('status', 'pending_approval')
            ->with(['organizer', 'venue'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($events);
    }

    public function approveEvent(Event $event): JsonResponse
    {
        if ($event->status !== 'pending_approval') {
            return response()->json(['message' => 'Event is not pending approval'], 422);
        }

        $event->update(['status' => 'published']);
        event(new EventPublished($event));

        logger("Event [{$event->id}] approved by admin");

        return response()->json(['message' => 'Event approved', 'event' => $event]);
    }

    public function rejectEvent(Request $request, Event $event): JsonResponse
    {
        if ($event->status !== 'pending_approval') {
            return response()->json(['message' => 'Event is not pending approval'], 422);
        }

        $request->validate(['reason' => 'nullable|string|max:1000']);

        $event->update(['status' => 'draft']);

        logger("Event [{$event->id}] rejected by admin. Reason: " . ($request->reason ?? 'none'));

        return response()->json(['message' => 'Event rejected', 'event' => $event]);
    }

    // ─── Users ─────────────────────────────────────────────────────────

    public function listUsers(Request $request): JsonResponse
    {
        $query = User::with('roles')->withCount('orders');

        if ($request->filled('role')) {
            $query->whereHas('roles', fn($q) => $q->where('role', $request->role)->where('is_active', true));
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        $users = $query->orderByDesc('created_at')->paginate(20);

        return response()->json($users);
    }

    public function toggleUserStatus(User $user): JsonResponse
    {
        $newStatus = $user->status === 'active' ? 'suspended' : 'active';
        $user->update(['status' => $newStatus]);

        logger("User [{$user->id}] status changed to [{$newStatus}] by admin");

        return response()->json(['message' => "User {$newStatus}", 'user' => $user]);
    }

    // ─── Organizers ────────────────────────────────────────────────────

    public function listOrganizers(Request $request): JsonResponse
    {
        $query = Organizer::with('user')->withCount('events');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('verified')) {
            $query->where('verified', $request->boolean('verified'));
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name_en', 'like', "%{$request->search}%")
                  ->orWhere('name_ar', 'like', "%{$request->search}%");
            });
        }

        $organizers = $query->orderByDesc('created_at')->paginate(20);

        return response()->json($organizers);
    }

    public function verifyOrganizer(Request $request, Organizer $organizer): JsonResponse
    {
        $request->validate([
            'verified' => 'required|boolean',
        ]);

        $organizer->update([
            'verified' => $request->verified,
            'verification_date' => $request->verified ? now() : null,
        ]);

        logger("Organizer [{$organizer->id}] verification set to [{$request->verified}] by admin");

        return response()->json(['message' => 'Organizer updated', 'organizer' => $organizer]);
    }

    // ─── Venues ────────────────────────────────────────────────────────

    public function listVenues(Request $request): JsonResponse
    {
        $query = Venue::with('user');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name_en', 'like', "%{$request->search}%")
                  ->orWhere('name_ar', 'like', "%{$request->search}%");
            });
        }

        $venues = $query->orderByDesc('created_at')->paginate(20);

        return response()->json($venues);
    }

    public function toggleVenueStatus(Venue $venue): JsonResponse
    {
        $newStatus = $venue->status === 'active' ? 'inactive' : 'active';
        $venue->update(['status' => $newStatus]);

        logger("Venue [{$venue->id}] status changed to [{$newStatus}] by admin");

        return response()->json(['message' => "Venue {$newStatus}", 'venue' => $venue]);
    }

    // ─── Settings ──────────────────────────────────────────────────────

    public function getSettings(): JsonResponse
    {
        $settings = SystemSetting::orderBy('group')->orderBy('key')->get();

        return response()->json($settings);
    }

    public function updateSetting(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'key' => 'required|string|max:100',
            'value' => 'nullable|string',
            'type' => 'sometimes|string|in:string,boolean,integer,float',
            'group' => 'sometimes|string|max:50',
            'description' => 'sometimes|nullable|string',
        ]);

        SystemConfig::set(
            $validated['key'],
            $validated['value'],
            $validated['type'] ?? 'string',
            $validated['group'] ?? 'general'
        );

        if (isset($validated['description'])) {
            SystemSetting::where('key', $validated['key'])
                ->update(['description' => $validated['description']]);
        }

        logger("System setting updated [{$validated['key']}]");

        return response()->json(['message' => 'Setting updated']);
    }
}
