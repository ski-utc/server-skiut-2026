<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RoomShotgunFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'numero' => $this->faker->unique()->numerify('###'),
            'nb_places' => $this->faker->randomElement([4, 6, 8]),
            'responsable_chambre' => $this->faker->optional()->email(),
            'ambiance' => $this->faker->randomElement(['mega grosse night', 'grosse night', 'petite night', 'calme']),
            'locked_until' => null,
            'locked_by_email' => null,
        ];
    }
}
