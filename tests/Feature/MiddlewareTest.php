<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Unit\JwtTestHelper;

/**
 * Tests pour les Middlewares (EnsureTokenIsValid et EnsureAdminTokenIsValid)
 */
class MiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $adminUser;
    protected $room;

    protected function setUp(): void
    {
        parent::setUp();

        $this->room = Room::factory()->create(['id' => 1]);

        $this->user = User::factory()->create([
            'id' => 1,
            'email' => 'test@example.com',
            'firstName' => 'Test',
            'lastName' => 'User',
            'admin' => false,
            'room_id' => $this->room->id,
        ]);

        $this->adminUser = User::factory()->create([
            'id' => 2,
            'email' => 'admin@example.com',
            'firstName' => 'Admin',
            'lastName' => 'User',
            'admin' => true,
            'room_id' => $this->room->id,
        ]);
    }

    // ================ EnsureTokenIsValid Tests ================

    public function test_valid_token_passes_middleware()
    {
        $token = JwtTestHelper::generateToken($this->user->id);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->getJson('/api/auth/me');

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));
    }

    public function test_missing_token_returns_400()
    {
        $response = $this->getJson('/api/auth/me');

        $this->assertEquals(400, $response->status());
        $this->assertTrue($response->json('JWT_ERROR'));
    }

    public function test_expired_token_returns_401()
    {
        $token = JwtTestHelper::generateExpiredToken($this->user->id);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->getJson('/api/auth/me');

        $this->assertEquals(401, $response->status());
        $this->assertTrue($response->json('JWT_ERROR'));
    }

    public function test_invalid_signature_token_returns_401()
    {
        $token = JwtTestHelper::generateInvalidSignatureToken($this->user->id);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->getJson('/api/auth/me');

        $this->assertEquals(401, $response->status());
        $this->assertTrue($response->json('JWT_ERROR'));
    }

    public function test_malformed_token_returns_400()
    {
        $token = JwtTestHelper::generateMalformedToken();

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->getJson('/api/auth/me');

        $this->assertEquals(400, $response->status());
        $this->assertTrue($response->json('JWT_ERROR'));
    }

    public function test_token_with_nonexistent_user_returns_404()
    {
        $token = JwtTestHelper::generateToken(9999);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->getJson('/api/auth/me');

        $this->assertEquals(404, $response->status());
        $this->assertTrue($response->json('JWT_ERROR'));
    }

    public function test_user_data_merged_into_request()
    {
        $token = JwtTestHelper::generateToken($this->user->id);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->getJson('/api/auth/me');

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));
        $this->assertEquals($this->user->id, $response->json('id'));
    }

    // ================ EnsureAdminTokenIsValid Tests ================

    public function test_valid_admin_token_passes_middleware()
    {
        $token = JwtTestHelper::generateToken($this->adminUser->id);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->getJson('/api/admin');

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));
    }

    public function test_non_admin_token_returns_403()
    {
        $token = JwtTestHelper::generateToken($this->user->id);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->getJson('/api/admin');

        $this->assertEquals(403, $response->status());
    }

    public function test_missing_admin_token_returns_400()
    {
        $response = $this->getJson('/api/admin');

        $this->assertEquals(400, $response->status());
        $this->assertTrue($response->json('JWT_ERROR'));
    }

    public function test_expired_admin_token_returns_401()
    {
        $token = JwtTestHelper::generateExpiredToken($this->adminUser->id);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->getJson('/api/admin');

        $this->assertEquals(401, $response->status());
        $this->assertTrue($response->json('JWT_ERROR'));
    }

    public function test_admin_token_with_nonexistent_user_returns_404()
    {
        $token = JwtTestHelper::generateToken(9999);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->getJson('/api/admin');

        $this->assertEquals(404, $response->status());
        $this->assertTrue($response->json('JWT_ERROR'));
    }
}
