<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Venue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VenueController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);
    }

    public function index(Request $request): JsonResponse
    {
        $query = Venue::active()->with('user');

        if ($request->filled('city')) {
            $query->byCity($request->city);
        }

        if ($request->filled('capacity_min')) {
            $query->where('capacity', '>=', $request->capacity_min);
        }

        $venues = $query->orderByDesc('verified')->paginate(20);

        return response()->json($venues);
    }

    public function show(Venue $venue): JsonResponse
    {
        $venue->load(['events' => function ($q) {
            $q->published()->upcoming();
        }]);

        return response()->json($venue);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'city' => 'required|string|max:255',
            'district' => 'nullable|string|max:255',
            'address_en' => 'nullable|string|max:255',
            'address_ar' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'capacity' => 'nullable|integer|min:1',
            'cover_image' => 'nullable|string|max:500',
            'gallery' => 'nullable|array',
            'amenities' => 'nullable|array',
            'email' => 'nullable|string|email|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $validated['user_id'] = $request->user()->id;
        $venue = Venue::create($validated);
        $request->user()->assignRole('venue_owner');

        return response()->json($venue, 201);
    }

    public function update(Request $request, Venue $venue): JsonResponse
    {
        if ($venue->user_id !== $request->user()->id && !$request->user()->hasRole('admin')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'name_en' => 'sometimes|string|max:255',
            'name_ar' => 'sometimes|nullable|string|max:255',
            'description_en' => 'sometimes|nullable|string',
            'description_ar' => 'sometimes|nullable|string',
            'city' => 'sometimes|string|max:255',
            'district' => 'sometimes|nullable|string|max:255',
            'address_en' => 'sometimes|nullable|string|max:255',
            'address_ar' => 'sometimes|nullable|string|max:255',
            'latitude' => 'sometimes|nullable|numeric',
            'longitude' => 'sometimes|nullable|numeric',
            'capacity' => 'sometimes|nullable|integer|min:1',
            'gallery' => 'sometimes|nullable|array',
            'amenities' => 'sometimes|nullable|array',
        ]);

        $venue->update($validated);

        return response()->json($venue);
    }
}
