<?php

namespace Tests\Feature\Controllers;

use App\Models\Room;
use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SkinderControllerTest extends TestCase
{
    private function getToken($user = null)
    {
        $user = $user ?: User::factory()->create();
        $room = Room::find($user->roomID);
        if (!$room) {
            $room = Room::factory()->create(['id' => $user->roomID]);
        }
        $payload = [
            'key' => $user->id,
            'exp' => time() + 3600,
        ];
        $privateKey = Config::get('services.crypt.private');
        return JWT::encode($payload, $privateKey, 'RS256');
    }

    public function test_get_profil_skinder_authenticated()
    {
        $token = $this->getToken();
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/getProfilSkinder');
        $response->assertStatus(200);
    }
    public function test_like_skinder_authenticated()
    {
        $token = $this->getToken();
        $response = $this->withHeader('Authorization', "Bearer $token")->postJson('/api/likeSkinder', [/* données de test */]);
        $response->assertStatus(200);
    }
    public function test_get_my_skinder_matches_authenticated()
    {
        $token = $this->getToken();
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/getMySkinderMatches');
        $response->assertStatus(200);
    }
    public function test_get_my_profil_skinder_authenticated()
    {
        $token = $this->getToken();
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/getMyProfilSkinder');
        $response->assertStatus(200);
    }
    public function test_modify_profil_skinder_authenticated()
    {
        $token = $this->getToken();
        $response = $this->withHeader('Authorization', "Bearer $token")->postJson('/api/modifyProfilSkinder', [/* données de test */]);
        $response->assertStatus(200);
    }
    public function test_upload_room_image_authenticated()
    {
        Storage::fake('public');
        $token = $this->getToken();

        $fakeImage = UploadedFile::fake()->image('room.jpg');

        $response = $this->withHeader('Authorization', "Bearer $token")
                        ->post('/api/uploadRoomImage', [
                            'roomId' => 1,
                            'image' => $fakeImage,
                        ]);
        $response->assertStatus(200);
    }

}
