<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function show(Order $order): JsonResponse
    {
        if ($order->user_id !== auth()->id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $order->load(['event', 'tickets', 'event.organizer']);

        return response()->json($order);
    }

    public function cancel(Order $order): JsonResponse
    {
        if ($order->user_id !== auth()->id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if (!$order->isPaid()) {
            $order->update(['status' => 'cancelled']);
            return response()->json(['message' => 'Order cancelled']);
        }

        return response()->json(['message' => 'Paid orders cannot be cancelled online. Contact support.'], 422);
    }
}
