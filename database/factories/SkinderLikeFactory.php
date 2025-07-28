<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SkinderLike>
 */
class SkinderLikeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'room_likeur' => fake()->numberBetween(1, 15),
            'room_liked' => fake()->numberBetween(1, 15),
        ];
    }
}
