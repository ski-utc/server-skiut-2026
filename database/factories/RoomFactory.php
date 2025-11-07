<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RoomFactory extends Factory
{
    public function definition(): array
    {
        $moods = ['Chill', 'Petite Night', 'Grosse Night', 'Mega Grosse Night'];
        $passions = [
            ['ski', 'raclette', 'after-ski'],
            ['musique', 'jeux', 'apéro'],
            ['binch', 'vin', 'vodka'],
            ['chicha', 'roulées', 'indus'],
            ['snowboard', 'raquettes', 'grotte du Yeti']
        ];

        return [
            'roomNumber' => fake()->unique()->numberBetween(100, 999),
            'capacity' => fake()->randomElement([4, 6]),
            'name' => fake()->unique()->words(2, true),
            'mood' => fake()->randomElement($moods),
            'photoPath' => fake()->optional()->imageUrl(640, 480, 'room'),
            'description' => fake()->optional()->words(10, true),
            'passions' => json_encode(fake()->randomElement($passions)),
            'totalPoints' => fake()->numberBetween(0, 1000),
            'user_id' => null,
        ];
    }
}
