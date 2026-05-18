<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Organizer;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketPurchaseTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Event $event;
    private TicketType $ticketType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->user->assignRole('attendee');

        $organizer = Organizer::factory()->create(['status' => 'active', 'verified' => true]);

        $this->event = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'created_by_user_id' => $organizer->user_id,
            'status' => 'published',
            'has_tickets' => true,
        ]);

        $this->ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'price' => 100.00,
            'quantity_total' => 100,
            'quantity_sold' => 0,
            'status' => 'available',
        ]);
    }

    public function test_user_can_purchase_ticket(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/tickets/purchase', [
            'event_id' => $this->event->id,
            'ticket_type_id' => $this->ticketType->id,
            'quantity' => 2,
            'customer_name' => 'Test Buyer',
            'customer_email' => 'buyer@example.com',
            'customer_phone' => '01000000000',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['order', 'payment']);
    }

    public function test_purchase_fails_for_sold_out_tickets(): void
    {
        $this->ticketType->update(['quantity_sold' => 100]);

        $response = $this->actingAs($this->user)->postJson('/api/tickets/purchase', [
            'event_id' => $this->event->id,
            'ticket_type_id' => $this->ticketType->id,
            'quantity' => 1,
            'customer_name' => 'Test Buyer',
            'customer_email' => 'buyer@example.com',
            'customer_phone' => '01000000000',
        ]);

        $response->assertStatus(422);
    }
}
