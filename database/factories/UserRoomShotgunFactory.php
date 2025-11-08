<?php

namespace Database\Factories;

use App\Models\RoomShotgun;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserRoomShotgunFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'room_shotgun_id' => RoomShotgun::factory(),
            'email' => $this->faker->unique()->email(),
            'is_vegetarian' => $this->faker->boolean(30),
        ];
    }
}
