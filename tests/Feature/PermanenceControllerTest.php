<?php

namespace Tests\Feature;

use App\Models\Permanence;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Unit\JwtTestHelper;

/**
 * Tests pour PermanenceController
 * Routes: /api/permanences/*, /api/admin/permanences/*
 */
class PermanenceControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $memberUser;
    protected $adminUser;
    protected $room;

    protected function setUp(): void
    {
        parent::setUp();

        $this->room = Room::factory()->create();
        $this->user = User::factory()->create([
            'room_id' => $this->room->id,
            'admin' => false,
            'member' => false,
        ]);

        $this->memberUser = User::factory()->create([
            'room_id' => $this->room->id,
            'admin' => false,
            'member' => true,
        ]);

        $this->adminUser = User::factory()->create([
            'room_id' => $this->room->id,
            'admin' => true,
            'member' => true,
        ]);
    }

    public function test_get_user_permanences_success()
    {
        $token = JwtTestHelper::generateToken($this->memberUser->id);
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->getJson('/api/permanences/my');

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));
    }

    public function test_get_all_permanences_success()
    {
        $token = JwtTestHelper::generateToken($this->adminUser->id);
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->getJson('/api/admin/permanences');

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));
    }

    public function test_get_permanence_by_id_success()
    {
        $token = JwtTestHelper::generateToken($this->adminUser->id);

        $permanence = Permanence::create([
            'name' => 'Original Permanence',
            'start_datetime' => now()->addDays(1),
            'end_datetime' => now()->addDays(1)->addHours(2),
            'responsible_user_id' => $this->adminUser->id,
            'notes' => 'Original',
            'location' => 'Room 101',
            'status' => 'scheduled',
        ]);

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->getJson("/api/permanences/{$permanence->id}");

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));
    }

    public function test_update_permanence_success()
    {
        $token = JwtTestHelper::generateToken($this->adminUser->id);

        $permanence = Permanence::create([
            'name' => 'Original Permanence',
            'start_datetime' => now()->addDays(1),
            'end_datetime' => now()->addDays(1)->addHours(2),
            'responsible_user_id' => $this->adminUser->id,
            'notes' => 'Original',
            'location' => 'Room 101',
            'status' => 'scheduled',
        ]);

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->putJson("/api/admin/permanences/{$permanence->id}", [
            'name' => 'Updated Permanence',
            'start_datetime' => now()->addDays(3)->toDateTimeString(),
            'end_datetime' => now()->addDays(3)->addHours(2)->toDateTimeString(),
            'responsible_user_id' => $this->adminUser->id,
            'notes' => 'Updated',
        ]);

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));

        $this->assertDatabaseHas('permanences', [
            'id' => $permanence->id,
            'name' => 'Updated Permanence',
        ]);
    }

    public function test_delete_permanence_success()
    {
        $token = JwtTestHelper::generateToken($this->adminUser->id);

        $permanence = Permanence::create([
            'name' => 'To Delete',
            'start_datetime' => now()->addDays(1),
            'end_datetime' => now()->addDays(1)->addHours(2),
            'responsible_user_id' => $this->adminUser->id,
            'notes' => 'Test',
            'location' => 'Room 101',
            'status' => 'scheduled',
        ]);

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->deleteJson("/api/admin/permanences/{$permanence->id}");

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));

        $this->assertDatabaseMissing('permanences', [
            'id' => $permanence->id,
        ]);
    }

    public function test_get_association_members_success()
    {
        $token = JwtTestHelper::generateToken($this->adminUser->id);
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->getJson('/api/admin/permanences/members');

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));
    }

    public function test_send_reminders_success()
    {
        $token = JwtTestHelper::generateToken($this->adminUser->id);
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->postJson('/api/admin/permanences/send-reminders');

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));
    }

    public function test_get_user_permanences_without_token()
    {
        $response = $this->getJson('/api/permanences/my');

        $this->assertEquals(401, $response->status());
    }

    public function test_get_all_permanences_without_permission()
    {
        $token = JwtTestHelper::generateToken($this->user->id);
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->getJson('/api/admin/permanences');

        $this->assertEquals(403, $response->status());
    }
}
