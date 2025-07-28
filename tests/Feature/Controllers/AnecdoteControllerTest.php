<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class AnecdoteControllerTest extends TestCase
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

    public function test_get_anecdotes_authenticated()
    {
        $token = $this->getToken();
        $response = $this->withHeader('Authorization', "Bearer $token")->postJson('/api/getAnecdotes', [/* données de test */]);
        $response->assertStatus(200);
    }
    public function test_like_anecdote_authenticated()
    {
        $token = $this->getToken();
        $response = $this->withHeader('Authorization', "Bearer $token")->postJson('/api/likeAnecdote', [/* données de test */]);
        $response->assertStatus(200);
    }
    public function test_warn_anecdote_authenticated()
    {
        $token = $this->getToken();
        $response = $this->withHeader('Authorization', "Bearer $token")->postJson('/api/warnAnecdote', [/* données de test */]);
        $response->assertStatus(200);
    }
    public function test_send_anecdote_authenticated()
    {
        $token = $this->getToken();
        $response = $this->withHeader('Authorization', "Bearer $token")->postJson('/api/sendAnecdote', [/* données de test */]);
        $response->assertStatus(200);
    }
    public function test_delete_anecdote_authenticated()
    {
        $token = $this->getToken();
        $response = $this->withHeader('Authorization', "Bearer $token")->postJson('/api/deleteAnecdote', [/* données de test */]);
        $response->assertStatus(200);
    }
}
