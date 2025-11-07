<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'cas' => fake()->unique()->userName(),
            'firstName' => fake()->firstName(),
            'lastName' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'room_id' => fake()->numberBetween(1, 15),
            'admin' => fake()->boolean(10),
            'member' => fake()->boolean(30),
            'alumniOrExte' => fake()->boolean(20),
        ];
    }
}
