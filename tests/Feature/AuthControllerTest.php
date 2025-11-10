<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Unit\JwtTestHelper;

/**
 * Tests pour AuthController
 * Routes: /api/auth/*
 */
class AuthControllerTest extends TestCase
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
            'admin' => false,
        ]);
    }

    public function test_get_user_data_success()
    {
        $token = JwtTestHelper::generateToken($this->user->id);
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->getJson('/api/auth/me');

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));
        $this->assertEquals($this->user->id, $response->json('id'));
    }

    public function test_get_user_data_without_token()
    {
        $response = $this->getJson('/api/auth/me');

        $this->assertEquals(400, $response->status());
        $this->assertTrue($response->json('JWT_ERROR'));
    }

    public function test_get_user_data_with_expired_token()
    {
        $token = JwtTestHelper::generateExpiredToken($this->user->id);
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->getJson('/api/auth/me');

        $this->assertEquals(401, $response->status());
    }

    public function test_refresh_token_success()
    {
        $refreshToken = JwtTestHelper::generateRefreshToken($this->user->id);
        $response = $this->withHeaders(['Authorization' => "Bearer {$refreshToken}"])->getJson('/auth/refresh');

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
    }
}
