<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Unit\JwtTestHelper;

/**
 * Tests pour ClassementController
 * Routes: /api/classement-*
 */
class ClassementControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $room;

    protected function setUp(): void
    {
        parent::setUp();

        $this->room = Room::factory()->create();
        $this->user = User::factory()->create([
            'room_id' => $this->room->id,
        ]);
    }

    public function test_classement_chambres_success()
    {
        $token = JwtTestHelper::generateToken($this->user->id);
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->getJson('/api/classement-chambres');
        
        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));
    }

    public function test_classement_performances_success()
    {
        $token = JwtTestHelper::generateToken($this->user->id);
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->getJson('/api/classement-performances');
        
        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));
    }

    public function test_classement_performances_by_type_distance()
    {
        $token = JwtTestHelper::generateToken($this->user->id);
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->getJson('/api/classement-performances?type=distance');
        
        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));
    }

    public function test_classement_performances_by_type_duration()
    {
        $token = JwtTestHelper::generateToken($this->user->id);
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->getJson('/api/classement-performances?type=duration');
        
        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));
    }

    public function test_classement_performances_by_type_speed()
    {
        $token = JwtTestHelper::generateToken($this->user->id);
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])->getJson('/api/classement-performances?type=speed');
        
        $this->assertGreaterThanOrEqual(200, $response->status());
        $this->assertLessThan(300, $response->status());
        $this->assertTrue($response->json('success'));
    }
}

