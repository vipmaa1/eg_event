<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Organizer;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private User $organizerUser;
    private Organizer $organizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('admin');

        $this->organizerUser = User::factory()->create();
        $this->organizerUser->assignRole('organizer');

        $this->organizer = Organizer::factory()->create([
            'user_id' => $this->organizerUser->id,
            'status' => 'active',
            'verified' => true,
        ]);
    }

    public function test_admin_dashboard_returns_stats(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/admin/dashboard');

        $response->assertOk()
            ->assertJsonStructure([
                'total_users', 'total_events', 'total_organizers',
                'total_venues', 'pending_approval', 'pending_organizers', 'total_revenue',
            ]);
    }

    public function test_non_admin_cannot_access_admin_routes(): void
    {
        $response = $this->actingAs($this->organizerUser)
            ->getJson('/api/admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_admin_can_list_all_events(): void
    {
        Event::factory()->count(3)->create([
            'organizer_id' => $this->organizer->id,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/admin/events');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_admin_can_delete_event(): void
    {
        $event = Event::factory()->create([
            'organizer_id' => $this->organizer->id,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->deleteJson("/api/admin/events/{$event->id}");

        $response->assertOk();
        $this->assertSoftDeleted($event);
    }

    public function test_admin_can_list_users(): void
    {
        User::factory()->count(2)->create();

        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/admin/users');

        $response->assertOk();
    }

    public function test_admin_can_toggle_user_status(): void
    {
        $user = User::factory()->create(['status' => 'active']);

        $response = $this->actingAs($this->adminUser)
            ->postJson("/api/admin/users/{$user->id}/toggle-status");

        $response->assertOk();
        $this->assertEquals('suspended', $user->fresh()->status);
    }

    public function test_admin_can_list_organizers(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/admin/organizers');

        $response->assertOk();
    }

    public function test_admin_can_verify_organizer(): void
    {
        $org = Organizer::factory()->create([
            'verified' => false,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson("/api/admin/organizers/{$org->id}/verify", [
                'verified' => true,
            ]);

        $response->assertOk();
        $this->assertTrue($org->fresh()->verified);
    }

    public function test_admin_can_list_venues(): void
    {
        $venue = Venue::factory()->create();

        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/admin/venues');

        $response->assertOk();
    }

    public function test_admin_can_toggle_venue_status(): void
    {
        $venue = Venue::factory()->create(['status' => 'active']);

        $response = $this->actingAs($this->adminUser)
            ->postJson("/api/admin/venues/{$venue->id}/toggle-status");

        $response->assertOk();
        $this->assertEquals('inactive', $venue->fresh()->status);
    }
}
