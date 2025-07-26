<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transport>
 */
class TransportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $routes = [
            ['Paris', 'Les 2 Alpes'],
            ['Compiègne', 'Les 2 Alpes'],
            ['Les 2 Alpes', 'Paris'],
            ['Les 2 Alpes', 'Compiègne'],
            ['Paris', 'Val Thorens'],
            ['Val Thorens', 'Paris'],
            ['Compiègne', 'Tignes'],
            ['Tignes', 'Compiègne'],
            ['Paris', 'Chamonix'],
            ['Chamonix', 'Paris']
        ];

        $route = fake()->randomElement($routes);
        $colours = ['Rouge', 'Bleu', 'Vert', 'Jaune', 'Orange', 'Violet', 'Rose', 'Blanc'];
        $types = ['aller', 'retour'];

        return [
            'departure' => $route[0],
            'arrival' => $route[1],
            'colour' => fake()->randomElement($colours),
            'type' => fake()->randomElement($types),
            'horaire_depart' => fake()->time(),
            'horaire_arrivee' => fake()->time(),
        ];
    }
} 