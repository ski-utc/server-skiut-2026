<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SkinderLikeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'room_liker_id' => fake()->numberBetween(1, 15),
            'room_liked_id' => fake()->numberBetween(1, 15),
        ];
    }
}
