<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Room>
 */
class RoomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
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
            'description' => fake()->optional()->paragraph(),
            'passions' => json_encode(fake()->randomElement($passions)),
            'totalPoints' => fake()->numberBetween(0, 1000),
            'userID' => null, // Sera assigné après création des users
        ];
    }
}
