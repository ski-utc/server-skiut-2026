<?php

namespace Tests\Feature\Controllers;

use App\Models\Challenge;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DefisControllerTest extends TestCase
{
    private function getToken($user = null)
    {
        $user = $user ?: User::factory()->create();
        $room = Room::find($user->room_id);
        if (!$room) {
            $room = Room::factory()->create(['id' => $user->room_id]);
        }
        $payload = [
            'key' => $user->id,
            'exp' => time() + 3600,
        ];
        $privateKey = Config::get('services.crypt.private');
        return JWT::encode($payload, $privateKey, 'RS256');
    }

    public function test_get_challenges_unauthenticated()
    {
        $response = $this->getJson('/api/challenges');
        $response->assertStatus(400);
    }
    public function test_get_challenges_authenticated()
    {
        $token = $this->getToken();
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/challenges');
        $response->assertStatus(200);
    }
    public function test_get_proof_image_authenticated()
    {
        $token = $this->getToken();
        $response = $this->withHeader('Authorization', "Bearer $token")->postJson('/api/challenges/getProofImage', [/* données de test */]);
        $response->assertStatus(200);
    }
    public function test_upload_proof_image_authenticated()
    {
        $now = Carbon::now();
        $challenge = Challenge::create(['title' => 'Test'.$now, 'nbPoints' => 10]);
        Storage::fake('public');
        $token = $this->getToken();

        $fakeImage = UploadedFile::fake()->image('proof.jpg');

        $response = $this->withHeader('Authorization', "Bearer $token")
                        ->post('/api/challenges/uploadProofImage', [
                            'defiId' => $challenge->id,
                            'media' => $fakeImage,
                        ]);
        $response->assertStatus(200);
    }
    public function test_delete_proof_image_authenticated()
    {
        $token = $this->getToken();
        $response = $this->withHeader('Authorization', "Bearer $token")->postJson('/api/challenges/deleteproofImage', [/* données de test */]);
        $response->assertStatus(200);
    }
    public function test_classement_chambres_authenticated()
    {
        $token = $this->getToken();
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/classement-chambres');
        $response->assertStatus(200);
    }
}
