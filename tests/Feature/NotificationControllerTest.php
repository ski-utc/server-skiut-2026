<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\Room;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Unit\JwtTestHelper;

/**
 * Tests pour NotificationController
 * Routes: /api/notifications/*, /api/admin/notifications/*
 */
class NotificationControllerTest extends TestCase
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

    public function test_get_notifications_success()
    {
        $token = JwtTestHelper::generateToken($this->user->id);
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->getJson('/api/notifications');
        
        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));
    }

    public function test_mark_as_read_success()
    {
        $token = JwtTestHelper::generateToken($this->user->id);
        $notification = Notification::factory()->create();
        
        
        UserNotification::create([
            'user_id' => $this->user->id,
            'notification_id' => $notification->id,
            'read' => false,
        ]);

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->postJson("/api/notifications/{$notification->id}/read", [
            'read' => true,
        ]);

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));
        
        
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $this->user->id,
            'notification_id' => $notification->id,
            'read' => true,
        ]);
    }

    public function test_get_admin_notifications_success()
    {
        $token = JwtTestHelper::generateToken($this->adminUser->id);
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->getJson('/api/admin/notifications');
        
        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));
    }

    public function test_get_recipients_data_success()
    {
        $token = JwtTestHelper::generateToken($this->adminUser->id);
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->getJson('/api/admin/notifications/recipients');
        
        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));
    }

    public function test_get_notifications_without_token()
    {
        $response = $this->getJson('/api/notifications');
        
        $this->assertEquals(400, $response->status());
    }

    public function test_admin_notifications_without_permission()
    {
        $token = JwtTestHelper::generateToken($this->user->id);
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->getJson('/api/admin/notifications');
        
        $this->assertEquals(403, $response->status());
    }
}

