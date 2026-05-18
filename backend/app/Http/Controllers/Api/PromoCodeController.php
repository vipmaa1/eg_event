<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromoCodeController extends Controller
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

        $codes = PromoCode::where('organizer_id', $organizer->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($codes);
    }

    public function store(Request $request): JsonResponse
    {
        $organizer = $request->user()->organizer;

        if (!$organizer) {
            return response()->json(['message' => 'Not an organizer'], 403);
        }

        $validated = $request->validate([
            'event_id' => 'nullable|exists:events,id',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0.01',
            'usage_limit' => 'nullable|integer|min:1',
            'per_user_limit' => 'nullable|integer|min:1',
            'min_order_amount' => 'nullable|numeric|min:0',
            'valid_from' => 'required|date',
            'valid_until' => 'required|date|after:valid_from',
        ]);

        $validated['organizer_id'] = $organizer->id;
        $validated['code'] = PromoCode::generateCode();

        $code = PromoCode::create($validated);

        return response()->json($code, 201);
    }

    public function checkValidity(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string',
            'event_id' => 'required|exists:events,id',
            'amount' => 'required|numeric|min:0',
        ]);

        $promo = PromoCode::where('code', $validated['code'])
            ->where(function ($q) use ($validated) {
                $q->whereNull('event_id')->orWhere('event_id', $validated['event_id']);
            })->first();

        if (!$promo || !$promo->isValid($validated['amount'])) {
            return response()->json(['valid' => false, 'message' => 'Invalid or expired promo code'], 422);
        }

        $discount = $promo->calculateDiscount($validated['amount']);

        return response()->json([
            'valid' => true,
            'code' => $promo->code,
            'discount_type' => $promo->discount_type,
            'discount_value' => $promo->discount_value,
            'discount_amount' => $discount,
        ]);
    }
}
