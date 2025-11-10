<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Unit\JwtTestHelper;

/**
 * Tests pour SkinderController
 * Routes: /api/skinder/*
 */
class SkinderControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $room;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');


        $this->room = Room::factory()->create([
            'photoPath' => 'storage/room_images/test_room.jpg',
        ]);

        Storage::disk('public')->put('room_images/test_room.jpg', 'fake_content');

        $this->user = User::factory()->create([
            'room_id' => $this->room->id,
        ]);
    }

    public function test_get_profil_skinder_success()
    {
        $token = JwtTestHelper::generateToken($this->user->id);


        $otherRoom = Room::factory()->create([
            'photoPath' => 'storage/room_images/other_room.jpg',
        ]);
        Storage::disk('public')->put('room_images/other_room.jpg', 'fake_content');

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->getJson('/api/skinder/profiles');

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));
    }

    public function test_like_skinder_success()
    {
        $token = JwtTestHelper::generateToken($this->user->id);
        $otherRoom = Room::factory()->create();

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->postJson("/api/skinder/profiles/{$otherRoom->id}/like", [
            'roomLiked' => $otherRoom->id,
        ]);

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));


        $this->assertDatabaseHas('skinder_likes', [
            'room_liker_id' => $this->room->id,
            'room_liked_id' => $otherRoom->id,
        ]);
    }

    public function test_get_my_skinder_matches_success()
    {
        $token = JwtTestHelper::generateToken($this->user->id);
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->getJson('/api/skinder/matches');

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));
    }

    public function test_get_my_profile_success()
    {
        $token = JwtTestHelper::generateToken($this->user->id);
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->getJson('/api/skinder/my-profile');

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));
    }

    public function test_upload_my_profile_image_success()
    {
        $token = JwtTestHelper::generateToken($this->user->id);
        $file = UploadedFile::fake()->image('profile.jpg');

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->postJson('/api/skinder/my-profile/image', [
            'image' => $file,
        ]);

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));
    }

    public function test_get_profil_skinder_without_token()
    {
        $response = $this->getJson('/api/skinder/profiles');

        $this->assertEquals(400, $response->status());
    }

    public function test_like_skinder_without_token()
    {
        $response = $this->postJson('/api/skinder/profiles/1/like');

        $this->assertEquals(400, $response->status());
    }
}
