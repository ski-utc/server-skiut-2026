<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

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

        $colourPairs = [
            ['colour' => '#a684ff', 'name' => 'Violet'],
            ['colour' => '#ff6467', 'name' => 'Rouge'],
            ['colour' => '#fcc800', 'name' => 'Jaune'],
            ['colour' => '#51a2ff', 'name' => 'Bleu'],
            ['colour' => '#05df72', 'name' => 'Vert'],
            ['colour' => '#fb64b6', 'name' => 'Rose'],
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
