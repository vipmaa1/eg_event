<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\PromoCode;
use App\Events\TicketPurchased;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TicketService
{
    private PaymentService $payment;

    public function __construct(PaymentService $payment)
    {
        $this->payment = $payment;
    }

    public function purchase(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $user = auth()->user();
            $event = Event::findOrFail($data['event_id']);
            $ticketType = TicketType::findOrFail($data['ticket_type_id']);

            if (!$ticketType->isAvailable()) {
                throw new \RuntimeException('Ticket type is not available');
            }

            $quantity = $data['quantity'];
            if (!$ticketType->hasCapacityFor($quantity)) {
                throw new \RuntimeException('Not enough tickets available');
            }

            $subtotal = $ticketType->price * $quantity;
            $fees = round($subtotal * 0.03, 2);
            $discount = 0;

            if (!empty($data['promo_code'])) {
                $promoCode = PromoCode::where('code', $data['promo_code'])
                    ->where(function ($q) use ($event) {
                        $q->whereNull('event_id')->orWhere('event_id', $event->id);
                    })->first();

                if (!$promoCode || !$promoCode->isValid($subtotal)) {
                    throw new \RuntimeException('Invalid or expired promo code');
                }

                $discount = $promoCode->calculateDiscount($subtotal);
                $promoCode->incrementUsage();
            }

            $total = $subtotal + $fees - $discount;

            $order = Order::create([
                'user_id' => $user->id,
                'event_id' => $event->id,
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'],
                'customer_phone' => $data['customer_phone'],
                'quantity' => $quantity,
                'subtotal_amount' => $subtotal,
                'discount_amount' => $discount,
                'fees_amount' => $fees,
                'total_amount' => $total,
                'currency' => $data['currency'] ?? 'EGP',
                'expires_at' => now()->addMinutes(30),
            ]);

            $paymentResult = $this->payment->createPaymobPayment([
                'amount' => $total,
                'currency' => $data['currency'] ?? 'EGP',
                'email' => $data['customer_email'],
                'phone' => $data['customer_phone'],
                'first_name' => explode(' ', $data['customer_name'])[0],
                'last_name' => explode(' ', $data['customer_name'])[1] ?? '',
            ]);

            if (!$paymentResult['success']) {
                $paymentResult = $this->payment->createFawryPayment([
                    'amount' => $total,
                    'currency' => $data['currency'] ?? 'EGP',
                    'email' => $data['customer_email'],
                    'phone' => $data['customer_phone'],
                    'name' => $data['customer_name'],
                    'reference' => $order->order_number,
                ]);
            }

            return [
                'order' => $order,
                'payment' => $paymentResult,
            ];
        });
    }

    public function confirmPayment(Order $order, string $transactionId, string $method): void
    {
        DB::transaction(function () use ($order, $transactionId, $method) {
            $order->markAsPaid($transactionId, $method);

            $tickets = [];
            for ($i = 0; $i < $order->quantity; $i++) {
                $ticket = Ticket::create([
                    'order_id' => $order->id,
                    'event_id' => $order->event_id,
                    'ticket_type_id' => $order->tickets()->first()?->ticket_type_id
                        ?? $order->event->ticketTypes()->first()->id,
                    'user_id' => $order->user_id,
                    'price_paid' => $order->total_amount / $order->quantity,
                    'currency' => $order->currency,
                    'holder_name' => $order->customer_name,
                    'holder_email' => $order->customer_email,
                    'holder_phone' => $order->customer_phone,
                    'qr_code_url' => $this->generateQrUrl($order->order_number, $i),
                    'status' => 'valid',
                ]);
                $tickets[] = $ticket;
            }

            $order->event->ticketTypes()
                ->where('id', $tickets[0]->ticket_type_id)
                ->increment('quantity_sold', $order->quantity);

            event(new TicketPurchased($order, $tickets));
        });
    }

    private function generateQrUrl(string $orderNumber, int $index): string
    {
        $code = $orderNumber . '-' . $index . '-' . Str::random(8);
        return route('api.tickets.qr', ['code' => $code]);
    }
}
