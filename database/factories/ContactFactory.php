<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contact>
 */
class ContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $roles = ['Prez officielle', 'Prez pas officielle', 'Resp Log', 'Resp Anim', 'Resp Info'];

        return [
            'name' => fake()->unique()->name(),
            'role' => fake()->randomElement($roles),
            'phoneNumber' => fake()->phoneNumber(),
        ];
    }
} 