<?php

namespace Tests\Feature;

use App\Models\Anecdote;
use App\Models\Challenge;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Unit\JwtTestHelper;

/**
 * Tests pour AdminController
 * Routes: /api/admin/*
 */
class AdminControllerTest extends TestCase
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

    public function test_get_admin_success()
    {
        $token = JwtTestHelper::generateToken($this->adminUser->id);
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->getJson('/api/admin');
        
        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));
    }

    public function test_get_admin_challenges_success()
    {
        $token = JwtTestHelper::generateToken($this->adminUser->id);
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->getJson('/api/admin/challenges');
        
        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));
    }

    public function test_get_challenge_details_success()
    {
        $token = JwtTestHelper::generateToken($this->adminUser->id);
        $challenge = Challenge::factory()->create();
        $challengeProof = \App\Models\ChallengeProof::factory()->create([
            'challenge_id' => $challenge->id,
            'room_id' => $this->room->id,
            'user_id' => $this->adminUser->id,
        ]);

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->getJson("/api/admin/challenges/{$challengeProof->id}");

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));
    }

    public function test_update_challenge_status_success()
    {
        $token = JwtTestHelper::generateToken($this->adminUser->id);
        $challenge = Challenge::factory()->create();
        $challengeProof = \App\Models\ChallengeProof::factory()->create([
            'challenge_id' => $challenge->id,
            'room_id' => $this->room->id,
            'user_id' => $this->adminUser->id,
        ]);

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->putJson("/api/admin/challenges/{$challengeProof->id}/status", [
            'is_valid' => true,
            'is_delete' => false,
        ]);

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));
        
        $this->assertDatabaseHas('challenge_proofs', ['id' => $challengeProof->id, 'valid' => true, 'delete' => false]);
    }

    public function test_get_admin_anecdotes_success()
    {
        $token = JwtTestHelper::generateToken($this->adminUser->id);
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->getJson('/api/admin/anecdotes');
        
        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));
    }

    public function test_get_anecdote_details_success()
    {
        $token = JwtTestHelper::generateToken($this->adminUser->id);
        $anecdote = Anecdote::factory()->create(['room_id' => $this->room->id, 'user_id' => $this->user->id]);

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->getJson("/api/admin/anecdotes/{$anecdote->id}");

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));
    }

    public function test_update_anecdote_status_success()
    {
        $token = JwtTestHelper::generateToken($this->adminUser->id);
        $anecdote = Anecdote::factory()->create(['room_id' => $this->room->id, 'user_id' => $this->user->id]);

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->putJson("/api/admin/anecdotes/{$anecdote->id}/status", [
            'is_valid' => true,
        ]);

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));
        
        $this->assertDatabaseHas('anecdotes', ['id' => $anecdote->id, 'valid' => true]);
    }

    public function test_get_admin_without_permission()
    {
        $token = JwtTestHelper::generateToken($this->user->id);
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->getJson('/api/admin');
        
        $this->assertEquals(403, $response->status());
    }

    public function test_get_admin_without_token()
    {
        $response = $this->getJson('/api/admin');
        
        $this->assertEquals(400, $response->status());
    }
}

