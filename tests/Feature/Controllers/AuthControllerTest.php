<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\Room;
use Illuminate\Support\Facades\Config;
use Firebase\JWT\JWT;

class AuthControllerTest extends TestCase
{
    private function getToken($user = null)
    {
        $user = $user ?: User::factory()->create();
        $room = Room::find($user->roomID);
        if(!$room) {
            $room = Room::factory()->create(['id' => $user->roomID]);
        }
        $payload = [
            'key' => $user->id,
            'exp' => time() + 3600,
        ];
        $privateKey = Config::get('services.crypt.private');
        return JWT::encode($payload, $privateKey, 'RS256');
    }

    public function test_get_user_data_unauthenticated()
    {
        $response = $this->getJson('/api/getUserData');
        $response->assertStatus(400);
    }

    public function test_get_user_data_authenticated()
    {
        $token = $this->getToken();
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/getUserData');
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'id', 'name', 'lastName', 'room', 'roomName', 'admin']);
    }
} 