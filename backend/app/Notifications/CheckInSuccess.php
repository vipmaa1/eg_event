<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CheckInSuccess extends Notification
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Check-in Successful - {$this->ticket->event->title_en}")
            ->line("You have been checked in to {$this->ticket->event->title_en}.")
            ->line("Enjoy the event!");
    }
}
