<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Unit\JwtTestHelper;

/**
 * Tests pour RgpdController
 * Routes: /api/rgpd/*
 */
class RgpdControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $adminUser;
    protected $room;

    protected function setUp(): void
    {
        parent::setUp();

        $this->room = Room::factory()->create();
        $this->user = User::factory()->create([
            'room_id' => $this->room->id,
            'admin' => false,
        ]);

        $this->adminUser = User::factory()->create([
            'room_id' => $this->room->id,
            'admin' => true,
        ]);
    }

    public function test_anonymize_my_data_success()
    {
        $token = JwtTestHelper::generateToken($this->user->id);
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->postJson('/api/rgpd/anonymize-my-data');

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'firstName' => 'Utilisateur',
            'lastName' => 'Anonymisé',
        ]);
    }

    public function test_delete_my_data_success()
    {
        $userId = $this->user->id;
        $token = JwtTestHelper::generateToken($userId);
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->deleteJson('/api/rgpd/delete-my-data');

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));

        $this->assertDatabaseMissing('users', [
            'id' => $userId,
        ]);
    }

    public function test_export_my_data_success()
    {
        $token = JwtTestHelper::generateToken($this->user->id);
        
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->getJson('/api/rgpd/export-my-data');

        $this->assertGreaterThanOrEqual(200, $response->getStatusCode());
        $this->assertLessThan(300, $response->getStatusCode());
        
        $tempPath = storage_path('app/temp');
        $zipFiles = glob($tempPath . '/mes_infos_*.zip');
        foreach ($zipFiles as $file) {
            @unlink($file);
        }
    }

    public function test_anonymize_all_data_success()
    {
        $token = JwtTestHelper::generateToken($this->adminUser->id);

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->postJson('/api/rgpd/anonymize-all-data', [
            'simde_key' => 'test_simde_key',
        ]);

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'firstName' => 'Utilisateur',
            'lastName' => 'Anonymisé',
        ]);
    }

    public function test_delete_all_data_success()
    {
        $userId = $this->user->id;
        $token = JwtTestHelper::generateToken($this->adminUser->id);

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->deleteJson('/api/rgpd/delete-all-data', [
            'simde_key' => 'test_simde_key',
        ]);

        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));

        $this->assertDatabaseMissing('users', [
            'id' => $userId,
        ]);
    }

    public function test_anonymize_my_data_without_token()
    {
        $response = $this->postJson('/api/rgpd/anonymize-my-data');

        $this->assertEquals(401, $response->status());
    }

    public function test_anonymize_all_data_without_permission()
    {
        $token = JwtTestHelper::generateToken($this->user->id);
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->postJson('/api/rgpd/anonymize-all-data');

        $this->assertEquals(403, $response->status());
    }
}
