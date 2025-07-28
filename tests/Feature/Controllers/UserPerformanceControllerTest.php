<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class UserPerformanceControllerTest extends TestCase
{
    private function getToken($user = null)
    {
        $user = $user ?: User::factory()->create();
        $payload = [
            'key' => $user->id,
            'exp' => time() + 3600,
        ];
        $privateKey = Config::get('services.crypt.private');
        return [$user, JWT::encode($payload, $privateKey, 'RS256')];
    }

    public function test_update_performance_authenticated()
    {
        $user = $this->getToken()[0];
        $token = $this->getToken()[1];
        $response = $this->withHeader('Authorization', "Bearer $token")->postJson('/api/update-performance', ['user_id' => $user->id, 'speed' => 100, 'distance' => 1000]);
        $response->assertStatus(200);
    }
}
