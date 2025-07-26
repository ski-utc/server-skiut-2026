<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Statistics>
 */
class StatisticsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startTime = fake()->dateTimeBetween('-1 month', 'now');
        $endTime = fake()->optional(0.8)->dateTimeBetween($startTime, '+8 hours');
        $locations = ['Les 2 Alpes', 'Val Thorens', 'Tignes', 'Chamonix', 'Courchevel', 'Méribel', 'La Plagne', 'Les Arcs', 'Val d\'Isère', 'Serre Chevalier'];

        return [
            'maximumSpeed' => fake()->numberBetween(20, 120),
            'startTime' => $startTime,
            'endTime' => $endTime,
            'location' => fake()->randomElement($locations),
            'userId' => fake()->numberBetween(1, 30),
        ];
    }
} 