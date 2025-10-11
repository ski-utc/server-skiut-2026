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

        // Tableau associatif pour lier les couleurs à leurs noms
        $colourPairs = [
            ['colour' => '#a684ff', 'name' => 'Purple'],
            ['colour' => '#ff6467', 'name' => 'Red'],
            ['colour' => '#fcc800', 'name' => 'Yellow'],
            ['colour' => '#51a2ff', 'name' => 'Blue'],
            ['colour' => '#05df72', 'name' => 'Green'],
            ['colour' => '#fb64b6', 'name' => 'Pink'],
            ['colour' => '#ff8904', 'name' => 'Orange'],
        ];

        $selectedColour = fake()->randomElement($colourPairs);
        $types = ['aller', 'retour'];

        return [
            'departure' => $route[0],
            'arrival' => $route[1],
            'colour' => $selectedColour['colour'],
            'colourName' => $selectedColour['name'],
            'type' => fake()->randomElement($types),
            'horaire_depart' => fake()->time(),
            'horaire_arrivee' => fake()->time(),
        ];
    }
}
