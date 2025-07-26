<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Firebase\JWT\JWT;

class UserControllerTest extends TestCase
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

    public function test_get_max_file_size_authenticated()
    {
        $token = $this->getToken();
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/getMaxFileSize');
        $response->assertStatus(200);
    }
    public function test_save_token_authenticated()
    {
        $token = $this->getToken();
        $response = $this->withHeader('Authorization', "Bearer $token")->postJson('/api/save-token', [/* données de test */]);
        $response->assertStatus(200);
    }
} 