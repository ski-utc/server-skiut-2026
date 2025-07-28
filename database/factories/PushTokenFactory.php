<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PushToken>
 */
class PushTokenFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'token' => fake()->regexify('[A-Za-z0-9]{152}'), // Format typique d'un token FCM
            'user_id' => fake()->numberBetween(1, 30),
        ];
    }
}
