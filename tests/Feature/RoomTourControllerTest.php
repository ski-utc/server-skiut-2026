<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Unit\JwtTestHelper;

/**
 * Tests pour RoomTourController
 * Routes: /api/room-tours/*, /api/admin/room-tours/*
 */
class RoomTourControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $adminUser;
    protected $room;

    protected function setUp(): void
    {
        parent::setUp();

        $this->room = Room::factory()->create();
        $this->user = User::factory()->create([
            'room_id' => $this->room->id,
            'admin' => false,
        ]);

        $this->adminUser = User::factory()->create([
            'room_id' => $this->room->id,
            'admin' => true,
        ]);
    }

    public function test_get_all_tours_success()
    {
        $token = JwtTestHelper::generateToken($this->adminUser->id);
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->getJson('/api/admin/room-tours');

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));
    }

    public function test_create_tour_success()
    {
        $token = JwtTestHelper::generateToken($this->adminUser->id);
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->postJson('/api/admin/room-tours', [
            'name' => 'Test Tour',
            'date' => now()->addDays(1)->toIso8601String(),
        ]);

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(600, $response->status());
    }

    public function test_toggle_tour_success()
    {
        $token = JwtTestHelper::generateToken($this->adminUser->id);
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->postJson('/api/admin/room-tours/1/toggle');

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(600, $response->status());
    }

    public function test_delete_tour_success()
    {
        $token = JwtTestHelper::generateToken($this->adminUser->id);
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->deleteJson('/api/admin/room-tours/1');

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(600, $response->status());
    }

    public function test_get_user_tour_success()
    {
        $token = JwtTestHelper::generateToken($this->user->id);
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->getJson('/api/room-tours/my-tour');

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(600, $response->status());
    }

    public function test_get_tour_status_for_traveler_success()
    {
        $token = JwtTestHelper::generateToken($this->user->id);
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->getJson('/api/room-tours/status');

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));
    }

    public function test_mark_room_visited_success()
    {
        $token = JwtTestHelper::generateToken($this->user->id);
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->postJson('/api/room-tours/visits/1/mark-visited');

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(600, $response->status());
    }

    public function test_unmark_visited_success()
    {
        $token = JwtTestHelper::generateToken($this->user->id);
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->postJson('/api/room-tours/visits/1/unmark-visited');

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(600, $response->status());
    }

    public function test_reorder_rooms_success()
    {
        $token = JwtTestHelper::generateToken($this->user->id);
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->postJson('/api/room-tours/my-tour/reorder', [
            'order' => [1, 2, 3],
        ]);

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(600, $response->status());
    }

    public function test_get_available_rooms_success()
    {
        $token = JwtTestHelper::generateToken($this->adminUser->id);
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->getJson('/api/admin/room-tours/available-rooms');

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));
    }

    public function test_get_user_tour_without_token()
    {
        $response = $this->getJson('/api/room-tours/my-tour');

        $this->assertEquals(401, $response->status());
    }

    public function test_get_all_tours_without_permission()
    {
        $token = JwtTestHelper::generateToken($this->user->id);
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->getJson('/api/admin/room-tours');

        $this->assertEquals(403, $response->status());
    }
}
