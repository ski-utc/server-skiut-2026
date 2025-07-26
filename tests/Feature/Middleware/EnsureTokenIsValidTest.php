<?php

namespace Tests\Feature\Middleware;

use App\Models\Room;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Firebase\JWT\JWT;

class EnsureTokenIsValidTest extends TestCase
{
    public function test_token_absent()
    {
        $response = $this->getJson('/api/getUserData');
        $response->assertStatus(400)
                 ->assertJson(['JWT_ERROR' => true]);
    }

    public function test_token_expired()
    {
        $user = User::factory()->create();
        $payload = [
            'key' => $user->id,
            'exp' => time() - 3600,
        ];
        $privateKey = Config::get('services.crypt.private');
        $token = JWT::encode($payload, $privateKey, 'RS256');
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/getUserData');
        $response->assertStatus(401)
                 ->assertJson(['JWT_ERROR' => true]);
    }

    // public function test_signature_invalide()
    // {
    //     $user = User::factory()->create();
    //     $payload = [
    //         'key' => $user->id,
    //         'exp' => time() + 3600,
    //     ];
    //     $wrongPrivateKey = <<<EOD
    //     -----BEGIN RSA PRIVATE KEY-----
    //     MIIBOgIBAAJBAKI+0Nvm1gQ+GQeBg9H3JQSp3db...
    //     -----END RSA PRIVATE KEY-----
    //     EOD;
    //     $token = JWT::encode($payload, $wrongPrivateKey, 'RS256');
    //     $response = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/getUserData');
    //     $response->assertStatus(401)
    //              ->assertJson(['JWT_ERROR' => true]);
    // }

    public function test_jwt_mal_forme()
    {
        $token = 'ceci.nest.pas.un.jwt';
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/getUserData');
        $response->assertStatus(400)
                 ->assertJson(['JWT_ERROR' => true]);
    }

    public function test_user_non_trouve()
    {
        $payload = [
            'key' => 99999999,
            'exp' => time() + 3600,
        ];
        $privateKey = Config::get('services.crypt.private');
        $token = JWT::encode($payload, $privateKey, 'RS256');
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/getUserData');
        $response->assertStatus(404)
                 ->assertJson(['JWT_ERROR' => true]);
    }

    public function test_token_valide()
    {
        $user = User::factory()->create();
        $room = Room::find($user->roomID);
        if(!$room) {
            $room = Room::factory()->create(['id' => $user->roomID]);
        }
        $payload = [
            'key' => $user->id,
            'exp' => time() + 3600,
        ];
        $privateKey = Config::get('services.crypt.private');
        $token = JWT::encode($payload, $privateKey, 'RS256');
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/getUserData');
        $response->assertStatus(200);
        // On peut aussi vérifier que la réponse contient bien les infos du user
        $response->assertJsonFragment(['id' => $user->id]);
    }
} 