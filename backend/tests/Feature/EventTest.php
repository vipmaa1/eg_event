<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Organizer;
use App\Models\User;
use App\Models\Venue;
use App\Services\SystemConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventTest extends TestCase
{
    use RefreshDatabase;

    private User $organizerUser;
    private User $adminUser;
    private Organizer $organizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->organizerUser = User::factory()->create();
        $this->organizerUser->assignRole('organizer');
        $this->organizer = Organizer::factory()->create([
            'user_id' => $this->organizerUser->id,
            'status' => 'active',
            'verified' => true,
        ]);

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('admin');
    }

    public function test_list_published_events(): void
    {
        Event::factory()->count(3)->create([
            'organizer_id' => $this->organizer->id,
            'status' => 'published',
        ]);

        $response = $this->getJson('/api/events');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_organizer_can_create_event(): void
    {
        $venue = Venue::factory()->create();

        $response = $this->actingAs($this->organizerUser)->postJson('/api/events', [
            'title_en' => 'New Test Event',
            'title_ar' => 'حدث اختبار جديد',
            'description_en' => 'Event description for testing',
            'start_date' => now()->addMonth()->toIso8601String(),
            'end_date' => now()->addMonth()->addDays(2)->toIso8601String(),
            'category' => 'concerts',
            'venue_id' => $venue->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'draft');
    }

    public function test_organizer_can_publish_event(): void
    {
        $event = Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'created_by_user_id' => $this->organizerUser->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->organizerUser)
            ->postJson("/api/events/{$event->id}/publish");

        $response->assertOk()
            ->assertJsonPath('event.status', 'published');
    }

    public function test_organizer_submits_for_approval_when_auto_approve_off(): void
    {
        SystemConfig::set('auto_approve_events', '0', 'boolean');

        $event = Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'created_by_user_id' => $this->organizerUser->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->organizerUser)
            ->postJson("/api/events/{$event->id}/publish");

        $response->assertOk()
            ->assertJsonPath('event.status', 'pending_approval')
            ->assertJsonPath('message', 'Event submitted for approval');
    }

    public function test_admin_can_approve_pending_event(): void
    {
        $event = Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'created_by_user_id' => $this->organizerUser->id,
            'status' => 'pending_approval',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson("/api/admin/events/{$event->id}/approve");

        $response->assertOk()
            ->assertJsonPath('event.status', 'published');
    }

    public function test_admin_can_reject_pending_event(): void
    {
        $event = Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'created_by_user_id' => $this->organizerUser->id,
            'status' => 'pending_approval',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson("/api/admin/events/{$event->id}/reject", [
                'reason' => 'Incomplete information',
            ]);

        $response->assertOk()
            ->assertJsonPath('event.status', 'draft');
    }

    public function test_non_admin_cannot_approve_events(): void
    {
        $event = Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'created_by_user_id' => $this->organizerUser->id,
            'status' => 'pending_approval',
        ]);

        $response = $this->actingAs($this->organizerUser)
            ->postJson("/api/admin/events/{$event->id}/approve");

        $response->assertStatus(403);
    }

    public function test_search_events(): void
    {
        Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'title_en' => 'Rock Concert Cairo',
            'status' => 'published',
        ]);

        $response = $this->getJson('/api/events?search=Rock');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }
}
