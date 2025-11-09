<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Unit\JwtTestHelper;

/**
 * Tests pour UserController (utilisé pour createOrUpdateUser et saveToken en interne)
 * Ce controller est principalement utilisé en interne par les callbacks OAuth
 */
class UserControllerTest extends TestCase
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

    public function test_user_exists_in_database()
    {
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'room_id' => $this->room->id,
        ]);
    }
}

