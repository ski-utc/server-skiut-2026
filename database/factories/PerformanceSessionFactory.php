<?php

namespace Database\Factories;

use App\Models\PerformanceSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PerformanceSession>
 */
class PerformanceSessionFactory extends Factory
{
    protected $model = PerformanceSession::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $maxSpeed = $this->faker->randomFloat(2, 10, 80); // Entre 10 et 80 km/h
        $averageSpeed = $this->faker->randomFloat(2, 5, $maxSpeed * 0.8); // Vitesse moyenne < vitesse max
        $duration = $this->faker->numberBetween(300, 7200); // Entre 5 minutes et 2 heures (en secondes)
        $distance = ($averageSpeed / 3.6) * $duration; // Distance en mètres

        return [
            'user_id' => User::factory(),
            'session_id' => Str::uuid(),
            'max_speed' => $maxSpeed,
            'average_speed' => $averageSpeed,
            'distance' => round($distance, 2),
            'duration' => $duration,
            'session_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }

    /**
     * Indicate that this is a short session.
     */
    public function short(): static
    {
        return $this->state(function (array $attributes) {
            $duration = $this->faker->numberBetween(300, 1800); // 5-30 minutes
            $averageSpeed = $this->faker->randomFloat(2, 5, 30);
            $distance = ($averageSpeed / 3.6) * $duration;

            return [
                'duration' => $duration,
                'average_speed' => $averageSpeed,
                'max_speed' => $this->faker->randomFloat(2, $averageSpeed, $averageSpeed + 20),
                'distance' => round($distance, 2),
            ];
        });
    }

    /**
     * Indicate that this is a long session.
     */
    public function long(): static
    {
        return $this->state(function (array $attributes) {
            $duration = $this->faker->numberBetween(3600, 14400); // 1-4 heures
            $averageSpeed = $this->faker->randomFloat(2, 15, 50);
            $distance = ($averageSpeed / 3.6) * $duration;

            return [
                'duration' => $duration,
                'average_speed' => $averageSpeed,
                'max_speed' => $this->faker->randomFloat(2, $averageSpeed + 5, 80),
                'distance' => round($distance, 2),
            ];
        });
    }

    /**
     * Indicate that this is a high-speed session.
     */
    public function highSpeed(): static
    {
        return $this->state(fn (array $attributes) => [
            'max_speed' => $this->faker->randomFloat(2, 60, 120),
            'average_speed' => $this->faker->randomFloat(2, 40, 80),
        ]);
    }
}
