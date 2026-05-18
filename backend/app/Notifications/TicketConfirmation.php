<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TicketConfirmation extends Notification
{
    use Queueable;

    public function __construct(
        public Order $order,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Ticket Confirmation - {$this->order->event->title_en}")
            ->greeting("Hello {$this->order->customer_name},")
            ->line("Your tickets for {$this->order->event->title_en} are confirmed.")
            ->line("Order: {$this->order->order_number}")
            ->line("Quantity: {$this->order->quantity}")
            ->line("Total: {$this->order->currency} {$this->order->total_amount}")
            ->action('View Tickets', url("/orders/{$this->order->order_number}"))
            ->line('Your QR code tickets are attached. Present them at the venue for entry.');
    }
}
