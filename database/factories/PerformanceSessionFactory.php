<?php

namespace Database\Factories;

use App\Models\PerformanceSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PerformanceSessionFactory extends Factory
{
    protected $model = PerformanceSession::class;

    public function definition(): array
    {
        $maxSpeed = $this->faker->randomFloat(2, 10, 80);
        $averageSpeed = $this->faker->randomFloat(2, 5, $maxSpeed * 0.8);
        $duration = $this->faker->numberBetween(300, 7200);
        $distance = ($averageSpeed / 3.6) * $duration;

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

    public function short(): static
    {
        return $this->state(function (array $attributes) {
            $duration = $this->faker->numberBetween(300, 1800);
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

    public function long(): static
    {
        return $this->state(function (array $attributes) {
            $duration = $this->faker->numberBetween(3600, 14400);
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

    public function highSpeed(): static
    {
        return $this->state(fn (array $attributes) => [
            'max_speed' => $this->faker->randomFloat(2, 60, 120),
            'average_speed' => $this->faker->randomFloat(2, 40, 80),
        ]);
    }
}
