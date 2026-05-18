<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\Event;
use Illuminate\Support\Facades\Log;

class CheckInService
{
    public function scan(array $data): array
    {
        $ticketCode = $data['ticket_code'] ?? null;
        $eventId = $data['event_id'] ?? null;
        $scannerUserId = auth()->id();

        if (!$ticketCode) {
            return ['success' => false, 'message' => 'Ticket code is required'];
        }

        $ticket = Ticket::where('ticket_code', $ticketCode)->first();

        if (!$ticket) {
            return ['success' => false, 'message' => 'Invalid ticket code'];
        }

        if ($eventId && $ticket->event_id !== (int) $eventId) {
            return ['success' => false, 'message' => 'Ticket does not belong to this event'];
        }

        if ($ticket->status === 'cancelled' || $ticket->status === 'refunded') {
            return ['success' => false, 'message' => "Ticket is {$ticket->status}"];
        }

        if ($ticket->checked_in) {
            return [
                'success' => false,
                'message' => 'Ticket already used',
                'data' => [
                    'checked_in_at' => $ticket->checked_in_at?->toIso8601String(),
                    'holder_name' => $ticket->holder_name,
                ],
            ];
        }

        $ticket->markAsUsed($scannerUserId);

        Log::info('Ticket checked in', [
            'ticket_code' => $ticketCode,
            'event_id' => $ticket->event_id,
            'scanned_by' => $scannerUserId,
        ]);

        return [
            'success' => true,
            'message' => 'Check-in successful',
            'data' => [
                'holder_name' => $ticket->holder_name,
                'ticket_type' => $ticket->ticketType?->name_en,
                'event_title' => $ticket->event?->title_en,
            ],
        ];
    }

    public function getEventCheckIns(int $eventId): array
    {
        $event = Event::findOrFail($eventId);
        $total = $event->tickets()->count();
        $checkedIn = $event->tickets()->where('checked_in', true)->count();

        return [
            'event_title' => $event->title_en,
            'total_tickets' => $total,
            'checked_in' => $checkedIn,
            'remaining' => $total - $checkedIn,
        ];
    }
}
