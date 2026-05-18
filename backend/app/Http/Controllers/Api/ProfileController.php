<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|nullable|string|max:20',
            'bio' => 'sometimes|nullable|string|max:1000',
            'profile_photo' => 'sometimes|nullable|string|max:500',
            'preferences' => 'sometimes|array',
        ]);

        $user->update($validated);

        return response()->json($user);
    }

    public function orders(Request $request): JsonResponse
    {
        $orders = $request->user()
            ->orders()
            ->with(['event', 'tickets'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($orders);
    }

    public function tickets(Request $request): JsonResponse
    {
        $tickets = $request->user()
            ->tickets()
            ->with(['event', 'ticketType', 'order'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($tickets);
    }
}
