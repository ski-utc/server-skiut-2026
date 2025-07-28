<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    private function getToken($user = null)
    {
        $user = $user ?: User::factory()->create();
        $payload = [
            'key' => $user->id,
            'exp' => time() + 3600,
        ];
        $privateKey = Config::get('services.crypt.private');
        return JWT::encode($payload, $privateKey, 'RS256');
    }

    public function test_get_random_data_unauthenticated()
    {
        $response = $this->getJson('/api/getRandomData');
        $response->assertStatus(400);
    }

    public function test_get_random_data_authenticated()
    {
        $token = $this->getToken();
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/getRandomData');
        $response->assertStatus(200);
        // Adapter les assertions selon la structure retournée
    }
}
