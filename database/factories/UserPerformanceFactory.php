<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserPerformance>
 */
class UserPerformanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => fake()->numberBetween(1, 30),
            'max_speed' => fake()->randomFloat(1, 15.0, 120.0),
            'total_distance' => fake()->randomFloat(2, 0.5, 50.0),
        ];
    }
}
