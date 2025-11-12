<?php

namespace Tests\Feature;

use App\Models\Challenge;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Unit\JwtTestHelper;

/**
 * Tests pour DefisController
 * Routes: /api/challenges/*
 */
class DefisControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $room;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->room = Room::factory()->create();
        $this->user = User::factory()->create([
            'room_id' => $this->room->id,
        ]);
    }

    public function test_get_challenges_success()
    {
        $token = JwtTestHelper::generateToken($this->user->id);
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->getJson('/api/challenges');

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));
    }

    public function test_get_proof_media_success()
    {
        $token = JwtTestHelper::generateToken($this->user->id);
        $challenge = Challenge::factory()->create();

        $proof = \App\Models\ChallengeProof::factory()->create([
            'challenge_id' => $challenge->id,
            'room_id' => $this->room->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->getJson("/api/challenges/proof-media/{$challenge->id}");

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));
    }

    public function test_get_max_file_size_success()
    {
        $token = JwtTestHelper::generateToken($this->user->id);
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->getJson('/api/challenges/max-file-size');

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));
    }

    public function test_upload_proof_media_success()
    {
        $token = JwtTestHelper::generateToken($this->user->id);
        $challenge = Challenge::factory()->create();
        $file = UploadedFile::fake()->image('proof.jpg');

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->postJson('/api/challenges/proof-media', [
            'defiId' => $challenge->id,
            'media' => $file,
            'mediaType' => 'image',
        ]);

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));
    }

    public function test_delete_proof_media_success()
    {
        $token = JwtTestHelper::generateToken($this->user->id);
        $challenge = Challenge::factory()->create();

        // Create a fake file first
        $filePath = 'defiProofImages/test_proof.jpg';
        Storage::disk('public')->put($filePath, 'fake_content');

        $proof = \App\Models\ChallengeProof::factory()->create([
            'challenge_id' => $challenge->id,
            'room_id' => $this->room->id,
            'user_id' => $this->user->id,
            'file' => 'storage/' . $filePath,
        ]);

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->deleteJson('/api/challenges/proof-media/1', [
            'defiId' => $challenge->id,
        ]);

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));
    }

    public function test_get_challenges_without_token()
    {
        $response = $this->getJson('/api/challenges');

        $this->assertEquals(401, $response->status());
    }
}
