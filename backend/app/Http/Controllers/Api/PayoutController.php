<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payout;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayoutController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request): JsonResponse
    {
        $organizer = $request->user()->organizer;

        if (!$organizer) {
            return response()->json(['message' => 'Not an organizer'], 403);
        }

        $payouts = Payout::where('organizer_id', $organizer->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($payouts);
    }

    public function store(Request $request): JsonResponse
    {
        $organizer = $request->user()->organizer;

        if (!$organizer) {
            return response()->json(['message' => 'Not an organizer'], 403);
        }

        $validated = $request->validate([
            'event_id' => 'nullable|exists:events,id',
            'amount' => 'required|numeric|min:1',
            'method' => 'required|in:bank_transfer,wallet,cash',
        ]);

        $payout = Payout::create([
            'organizer_id' => $organizer->id,
            'event_id' => $validated['event_id'] ?? null,
            'amount' => $validated['amount'],
            'currency' => 'EGP',
            'platform_fee' => round($validated['amount'] * 0.05, 2),
            'net_amount' => $validated['amount'] - round($validated['amount'] * 0.05, 2),
            'status' => 'pending',
            'method' => $validated['method'],
        ]);

        return response()->json($payout, 201);
    }
}
