<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Firebase\JWT\JWT;

class NotificationControllerTest extends TestCase
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

    public function test_get_notifications_unauthenticated()
    {
        $response = $this->getJson('/api/getNotifications');
        $response->assertStatus(400);
    }

    public function test_get_notifications_authenticated()
    {
        $token = $this->getToken();
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/getNotifications');
        $response->assertStatus(200);
        // Adapter les assertions selon la structure retournée
    }
} 