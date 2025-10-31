<?php

namespace Tests\Feature\Controllers;

use App\Models\PerformanceSession;
use App\Models\User;
use App\Models\UserPerformance;
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
        [$user, $token] = $this->getToken();
        
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/update-performance', [
                'speed' => 100,
                'distance' => 1000,
                'duration' => 3600,
                'average_speed' => 80
            ]);
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'session',
                'global_performance',
                'session_count'
            ]
        ]);
        $response->assertJson([
            'success' => true
        ]);
        
        // Vérifier que la session a été créée
        $this->assertDatabaseHas('performance_sessions', [
            'user_id' => $user->id,
            'max_speed' => 100.0,
            'distance' => 1000.0
        ]);
        
        // Vérifier que la performance globale a été créée/mise à jour
        $this->assertDatabaseHas('users_performances', [
            'user_id' => $user->id,
            'max_speed' => 100.0,
            'total_distance' => 1000.0
        ]);
    }
}
