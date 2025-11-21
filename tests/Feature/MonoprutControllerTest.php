<?php

namespace Tests\Feature;

use App\Models\Monoprut;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Unit\JwtTestHelper;

/**
 * Tests pour MonoprutController
 * Routes: /api/articles/*
 */
class MonoprutControllerTest extends TestCase
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

    public function test_get_articles_success()
    {
        $token = JwtTestHelper::generateToken($this->user->id);
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->getJson('/api/articles');

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));
    }

    public function test_create_article_success()
    {
        $token = JwtTestHelper::generateToken($this->user->id);
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->postJson('/api/articles', [
            'product' => 'Test Product',
            'quantity' => 5,
            'type' => 'fruit',
        ]);

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));

        $this->assertDatabaseHas('monoprut', ['product' => 'Test Product', 'giver_room_id' => $this->room->id]);
    }

    public function test_shotgun_article_success()
    {
        $token = JwtTestHelper::generateToken($this->user->id);

        $giverRoom = Room::factory()->create();
        $article = Monoprut::factory()->create([
            'giver_room_id' => $giverRoom->id,
            'receiver_room_id' => null,
        ]);

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->postJson("/api/articles/{$article->id}/shotgun");

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));

        $this->assertDatabaseHas('monoprut', ['id' => $article->id, 'receiver_room_id' => $this->room->id]);
    }

    public function test_my_given_articles_success()
    {
        $token = JwtTestHelper::generateToken($this->user->id);
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->getJson('/api/articles/given');

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));
    }

    public function test_my_received_articles_success()
    {
        $token = JwtTestHelper::generateToken($this->user->id);
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->getJson('/api/articles/received');

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));
    }

    public function test_mark_as_retrieved_success()
    {
        $token = JwtTestHelper::generateToken($this->user->id);

        $giverRoom = Room::factory()->create();
        $article = Monoprut::factory()->create([
            'giver_room_id' => $giverRoom->id,
            'receiver_room_id' => $this->room->id,
            'retrieved' => false,
        ]);

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->putJson("/api/articles/{$article->id}/retrieve");

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));

        $this->assertDatabaseHas('monoprut', ['id' => $article->id, 'retrieved' => true]);
    }

    public function test_cancel_reservation_success()
    {
        $token = JwtTestHelper::generateToken($this->user->id);

        $giverRoom = Room::factory()->create();
        $article = Monoprut::factory()->create([
            'giver_room_id' => $giverRoom->id,
            'receiver_room_id' => $this->room->id,
            'retrieved' => false,
        ]);

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->postJson("/api/articles/{$article->id}/cancel-reservation");

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));

        $this->assertDatabaseHas('monoprut', ['id' => $article->id, 'receiver_room_id' => null]);
    }

    public function test_delete_article_success()
    {
        $token = JwtTestHelper::generateToken($this->user->id);

        $article = Monoprut::factory()->create([
            'giver_room_id' => $this->room->id,
            'receiver_room_id' => null,
        ]);

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->deleteJson("/api/articles/{$article->id}");

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));

        $this->assertDatabaseMissing('monoprut', ['id' => $article->id]);
    }

    public function test_get_articles_without_token()
    {
        $response = $this->getJson('/api/articles');

        $this->assertEquals(401, $response->status());
    }
}
