<?php

namespace Database\Factories;

use App\Models\Permanence;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PermanenceFactory extends Factory
{
    protected $model = Permanence::class;

    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('now', '+30 days');
        $duration = $this->faker->numberBetween(1, 4);
        $endDate = (clone $startDate)->modify("+{$duration} hours");

        $permanenceTypes = [
            'Maintenance matériel',
            'Accueil voyageurs',
            'Gestion logistique',
            'Animation soirée',
            'Nettoyage espaces communs',
            'Préparation repas',
            'Surveillance matériel',
            'Organisation activités',
            'Support technique',
            'Coordination transport'
        ];

        $locations = [
            'Hall d\'accueil',
            'Salle de matériel',
            'Cuisine',
            'Salle commune',
            'Bureau organisation',
            'Local technique',
            'Réception',
            'Salle de sport',
            'Terrasse',
            'Parking'
        ];

        return [
            'name' => $this->faker->randomElement($permanenceTypes),
            'description' => $this->faker->paragraph(2),
            'start_datetime' => $startDate,
            'end_datetime' => $endDate,
            'location' => $this->faker->randomElement($locations),
            'status' => $this->faker->randomElement(['scheduled', 'in_progress', 'completed', 'cancelled']),
            'responsible_user_id' => User::factory(),
            'notes' => $this->faker->optional(0.3)->paragraph(),
            'notification_sent' => $this->faker->boolean(20),
        ];
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'scheduled',
            'start_datetime' => $this->faker->dateTimeBetween('+1 day', '+30 days'),
            'notification_sent' => false,
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(function (array $attributes) {
            $now = now();
            $startTime = $this->faker->dateTimeBetween('-2 hours', 'now');
            $endTime = (clone $startTime)->modify('+3 hours');

            return [
                'status' => 'in_progress',
                'start_datetime' => $startTime,
                'end_datetime' => $endTime,
                'notification_sent' => true,
            ];
        });
    }

    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            $endTime = $this->faker->dateTimeBetween('-7 days', '-1 hour');
            $startTime = (clone $endTime)->modify('-2 hours');

            return [
                'status' => 'completed',
                'start_datetime' => $startTime,
                'end_datetime' => $endTime,
                'notification_sent' => true,
                'notes' => $this->faker->optional(0.7)->paragraph(),
            ];
        });
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'notes' => 'Permanence annulée - ' . $this->faker->sentence(),
        ]);
    }

    public function urgent(): static
    {
        return $this->state(function (array $attributes) {
            $startTime = $this->faker->dateTimeBetween('+1 hour', '+6 hours');
            $endTime = (clone $startTime)->modify('+2 hours');

            return [
                'start_datetime' => $startTime,
                'end_datetime' => $endTime,
                'status' => 'scheduled',
                'notification_sent' => false,
            ];
        });
    }
}
