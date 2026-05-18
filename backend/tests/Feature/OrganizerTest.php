<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Organizer;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Organizer $organizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->user->assignRole('organizer');

        $this->organizer = Organizer::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
            'verified' => true,
        ]);
    }

    public function test_organizer_dashboard_returns_stats_and_events(): void
    {
        $event = Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'created_by_user_id' => $this->user->id,
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/organizers/mine');

        $response->assertOk()
            ->assertJsonStructure([
                'organizer', 'total_events', 'events', 'total_tickets_sold',
                'total_revenue', 'pending_events',
            ])
            ->assertJsonPath('total_events', 1);
    }

    public function test_organizer_dashboard_includes_ticket_counts(): void
    {
        $event = Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'created_by_user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/organizers/mine');

        $response->assertOk();
        $events = $response->json('events');
        $this->assertArrayHasKey('tickets_count', $events[0]);
        $this->assertArrayHasKey('checked_in_count', $events[0]);
    }

    public function test_non_organizer_cannot_access_dashboard(): void
    {
        $attendee = User::factory()->create();
        $attendee->assignRole('attendee');

        $response = $this->actingAs($attendee)
            ->getJson('/api/organizers/mine');

        $response->assertStatus(403);
    }

    public function test_organizer_can_update_event(): void
    {
        $event = Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'created_by_user_id' => $this->user->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/events/{$event->id}", [
                'title_en' => 'Updated Event Title',
            ]);

        $response->assertOk()
            ->assertJsonPath('title_en', 'Updated Event Title');
    }

    public function test_organizer_can_cancel_own_event(): void
    {
        $event = Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'created_by_user_id' => $this->user->id,
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/events/{$event->id}/cancel");

        $response->assertOk();
        $this->assertEquals('cancelled', $event->fresh()->status);
    }
}
