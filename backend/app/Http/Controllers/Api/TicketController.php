<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\Order;
use App\Services\TicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function __construct(
        private TicketService $ticketService,
    ) {}

    public function purchase(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'ticket_type_id' => 'required|exists:ticket_types,id',
            'quantity' => 'required|integer|min:1|max:10',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'promo_code' => 'nullable|string|max:50',
            'currency' => 'sometimes|string|size:3',
        ]);

        try {
            $result = $this->ticketService->purchase($validated);
            return response()->json($result, 201);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function confirmPayment(Request $request, Order $order): JsonResponse
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'transaction_id' => 'required|string',
            'method' => 'required|in:paymob,fawry,cash',
        ]);

        try {
            $this->ticketService->confirmPayment($order, $validated['transaction_id'], $validated['method']);
            return response()->json(['message' => 'Payment confirmed', 'order' => $order->fresh()->load('tickets')]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function showQr(string $code): JsonResponse
    {
        $ticket = Ticket::where('ticket_code', $code)->firstOrFail();

        return response()->json([
            'ticket_code' => $ticket->ticket_code,
            'event_title' => $ticket->event->title_en,
            'holder_name' => $ticket->holder_name,
            'status' => $ticket->status,
            'checked_in' => $ticket->checked_in,
        ]);
    }

    public function getQrCode(Request $request, Ticket $ticket): JsonResponse
    {
        if ($ticket->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json([
            'ticket_code' => $ticket->ticket_code,
            'qr_code_url' => $ticket->qr_code_url,
        ]);
    }
}
