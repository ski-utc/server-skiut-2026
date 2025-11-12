<?php

namespace Tests\Feature;

use App\Models\PushToken;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Unit\JwtTestHelper;

/**
 * Tests pour PushTokenController
 * Routes: /api/push-tokens/*
 */
class PushTokenControllerTest extends TestCase
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

    public function test_store_push_token_success()
    {
        $token = JwtTestHelper::generateToken($this->user->id);
        $pushTokenValue = 'test_token_' . time();

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->postJson('/api/push-tokens', [
            'token' => $pushTokenValue,
            'device_type' => 'ios',
            'device_name' => 'iPhone 15',
        ]);

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));

        $this->assertDatabaseHas('push_tokens', ['user_id' => $this->user->id, 'token' => $pushTokenValue]);
    }

    public function test_index_push_tokens_success()
    {
        $token = JwtTestHelper::generateToken($this->user->id);
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->getJson('/api/push-tokens');

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));
    }

    public function test_deactivate_push_token_success()
    {
        $token = JwtTestHelper::generateToken($this->user->id);


        $pushTokenValue = 'test_token_' . time();
        PushToken::create([
            'user_id' => $this->user->id,
            'token' => $pushTokenValue,
            'device_type' => 'ios',
            'active' => true,
        ]);

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->postJson('/api/push-tokens/deactivate', [
            'token' => $pushTokenValue,
        ]);

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));


        $this->assertDatabaseHas('push_tokens', ['token' => $pushTokenValue, 'active' => false]);
    }

    public function test_destroy_push_token_success()
    {
        $token = JwtTestHelper::generateToken($this->user->id);


        $pushTokenValue = 'test_token_' . time();
        PushToken::create([
            'user_id' => $this->user->id,
            'token' => $pushTokenValue,
            'device_type' => 'android',
            'active' => true,
        ]);

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->deleteJson('/api/push-tokens', [
            'token' => $pushTokenValue,
        ]);

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));


        $this->assertDatabaseMissing('push_tokens', ['token' => $pushTokenValue]);
    }

    public function test_store_push_token_without_token()
    {
        $response = $this->postJson('/api/push-tokens', [
            'device_type' => 'ios',
        ]);

        $this->assertEquals(401, $response->status());
    }
}
