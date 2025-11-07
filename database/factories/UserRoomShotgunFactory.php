<?php

namespace Database\Factories;

use App\Models\RoomShotgun;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserRoomShotgunFactory extends Factory
{
    public function definition(): array
    {
        return [
            'room_shotgun_id' => RoomShotgun::factory(),
            'email' => $this->faker->unique()->email(),
            'is_vegetarian' => $this->faker->boolean(30),
        ];
    }
}

