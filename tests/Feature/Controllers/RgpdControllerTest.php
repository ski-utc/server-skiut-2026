<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Firebase\JWT\JWT;

class RgpdControllerTest extends TestCase
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

    private function getSimdeKey()
    {
        return env('SIMDE_KEY');
    }

    public function test_anonymize_my_data_authenticated()
    {
        $token = $this->getToken();
        $response = $this->withHeader('Authorization', "Bearer $token")->postJson('/api/rgpd/anonymize-my-data', [/* données de test */]);
        $response->assertStatus(200);
    }
    public function test_delete_my_data_authenticated()
    {
        $token = $this->getToken();
        $response = $this->withHeader('Authorization', "Bearer $token")->postJson('/api/rgpd/delete-my-data', [/* données de test */]);
        $response->assertStatus(200);
    }
    public function test_export_my_data_authenticated()
    {
        $token = $this->getToken();
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/rgpd/export-my-data');
        $response->assertStatus(200);
    }
    public function test_anonymize_all_data_unauthenticated()
    {
        $response = $this->postJson('/api/rgpd/anonymize-all-data', [/* données de test */]);
        $response->assertStatus(403);
    }
    public function test_anonymize_all_data_authenticated()
    {
        $simdeKey = $this->getSimdeKey();
        $response = $this->postJson('/api/rgpd/anonymize-all-data', ['simde_key' => $simdeKey], /* données de test */);
        $response->assertStatus(200);
    }
    public function test_delete_all_data_unauthenticated()
    {
        $response = $this->postJson('/api/rgpd/delete-all-data', [/* données de test */]);
        $response->assertStatus(403);
    }
    public function test_delete_all_data_authenticated()
    {
        $simdeKey = $this->getSimdeKey();
        $response = $this->postJson('/api/rgpd/delete-all-data', ['simde_key' => $simdeKey], /* données de test */);
        $response->assertStatus(200);
    }
} 