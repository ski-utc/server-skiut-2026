<?php

namespace Tests\Feature;

use App\Models\Anecdote;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Unit\JwtTestHelper;

/**
 * Tests pour AnecdoteController
 * Routes: /api/anecdotes/*
 */
class AnecdoteControllerTest extends TestCase
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

    public function test_get_anecdotes_success()
    {
        $token = JwtTestHelper::generateToken($this->user->id);
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->getJson('/api/anecdotes');

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));
        $this->assertIsArray($response->json('data'));
    }

    public function test_send_anecdote_success()
    {
        $token = JwtTestHelper::generateToken($this->user->id);
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->postJson('/api/anecdotes', [
            'texte' => 'Test anecdote content',
        ]);

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));

        $this->assertDatabaseHas('anecdotes', ['text' => 'Test anecdote content']);
    }

    public function test_like_anecdote_success()
    {
        $token = JwtTestHelper::generateToken($this->user->id);
        $anecdote = Anecdote::factory()->create(['room_id' => $this->room->id, 'user_id' => $this->user->id]);

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->postJson("/api/anecdotes/{$anecdote->id}/like", [
            'like' => true,
        ]);

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));

        $this->assertDatabaseHas('anecdotes_likes', [
            'user_id' => $this->user->id,
            'anecdote_id' => $anecdote->id,
        ]);
    }

    public function test_warn_anecdote_success()
    {
        $token = JwtTestHelper::generateToken($this->user->id);
        $anecdote = Anecdote::factory()->create(['room_id' => $this->room->id, 'user_id' => $this->user->id]);

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->postJson("/api/anecdotes/{$anecdote->id}/warn", [
            'warn' => true,
        ]);

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));
    }

    public function test_delete_anecdote_success()
    {
        $token = JwtTestHelper::generateToken($this->user->id);
        $anecdote = Anecdote::factory()->create(['user_id' => $this->user->id, 'room_id' => $this->room->id]);

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->deleteJson("/api/anecdotes/{$anecdote->id}");

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));

        $this->assertDatabaseMissing('anecdotes', ['id' => $anecdote->id]);
    }

    public function test_get_anecdotes_without_token()
    {
        $response = $this->getJson('/api/anecdotes');

        $this->assertEquals(400, $response->status());
    }
}
