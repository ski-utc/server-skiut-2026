<?php

namespace Tests\Feature;

use App\Models\PerformanceSession;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Unit\JwtTestHelper;

/**
 * Tests pour PerformanceController
 * Routes: /api/user-performances/*
 */
class PerformanceControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $room;

    protected function setUp(): void
    {
        parent::setUp();

        $this->room = Room::factory()->create();
        $this->user = User::factory()->create([
            'room_id' => $this->room->id,
        ]);
    }

    public function test_update_performance_success()
    {
        $token = JwtTestHelper::generateToken($this->user->id);
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->putJson('/api/create-performance', [
            'speed' => 50.5,
            'distance' => 1000,
            'duration' => 120,
            'average_speed' => 45.2,
        ]);

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));


        $this->assertDatabaseHas('performance_sessions', [
            'user_id' => $this->user->id,
            'max_speed' => 50.5,
            'distance' => 1000,
        ]);
    }

    public function test_get_user_performances_success()
    {
        $token = JwtTestHelper::generateToken($this->user->id);
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->getJson('/api/user-performances');

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));
    }

    public function test_delete_performance_session_success()
    {
        $token = JwtTestHelper::generateToken($this->user->id);


        $sessionId = 'test_session_' . time();
        PerformanceSession::create([
            'user_id' => $this->user->id,
            'session_id' => $sessionId,
            'max_speed' => 50,
            'average_speed' => 45,
            'distance' => 1000,
            'duration' => 120,
            'session_date' => now(),
        ]);

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->deleteJson("/api/user-performances/{$sessionId}");

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));


        $this->assertDatabaseMissing('performance_sessions', ['session_id' => $sessionId]);
    }

    public function test_update_performance_without_token()
    {
        $response = $this->putJson('/api/create-performance', [
            'speed' => 50.5,
            'distance' => 1000,
        ]);

        $this->assertEquals(401, $response->status());
    }
}
