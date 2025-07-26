<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Activity>
 */
class ActivityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $activities = [
            'Cours de ski débutant' => ['08:00:00', '10:00:00'],
            'Cours de snowboard' => ['10:00:00', '12:00:00'],
            'Compétition de slalom' => ['14:00:00', '16:00:00'],
            'Sortie poudreuse' => ['09:00:00', '12:00:00'],
            'Freestyle session' => ['13:00:00', '15:00:00'],
            'Ski de randonnée' => ['07:00:00', '11:00:00'],
            'Cours de freeride' => ['10:00:00', '13:00:00'],
            'Compétition de vitesse' => ['15:00:00', '17:00:00'],
            'Ski nocturne' => ['19:00:00', '21:00:00'],
            'Laser Game' => ['11:00:00', '13:00:00'],
            'Sortie en chambres' => ['10:00:00', '16:00:00'],
            'Cours de snow bourré' => ['09:00:00', '11:00:00'],
            'Compétition de saut' => ['14:00:00', '16:00:00'],
            'Ski de fond' => ['08:00:00', '10:00:00'],
            'Cours de ski bourré' => ['13:00:00', '15:00:00']
        ];

        $activity = fake()->randomElement(array_keys($activities));
        $times = $activities[$activity];

        return [
            'date' => fake()->dateTimeBetween('-3 days', '+3 days')->format('Y-m-d'),
            'text' => $activity,
            'startTime' => $times[0],
            'endTime' => $times[1],
            'payant' => fake()->boolean(30),
        ];
    }
} 