<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $notifications = [
            'Bataille de boules de neige' => 'grosse bataille de boule de neiges à 17h en bas du télésiège',
            'After-Ski ce soir 18h30' => 'after-ski à 18h30 dans le bar du villaaaage',
            'Laser game annulé' => 'Malaise la team, laser game annulé (rip Marion)',
            '300 points pour du PQ' => 'SVP on a plus de PQ en A302, 300 points à celleux qui nous en ramènent',
            'Raf Pasquale a détruit les chiottes' => 'Les chiottes du 3e sont dead, raf pasquale a mangé un yab pas frais',
            'Les pistes de gauche sont fermées' => 'Les pistes de gauche sont fermées, y a pas assez de neige',
            'Descente de ski Redbull 14h' => 'Descente de ski Redbull à 14h en haut des pioches',
        ];

        $title = fake()->randomElement(array_keys($notifications));
        $description = $notifications[$title];

        return [
            'title' => $title,
            'description' => $description,
            'general' => fake()->boolean(70),
            'display' => fake()->boolean(10),
        ];
    }
}
