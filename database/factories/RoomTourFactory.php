<?php

namespace Database\Factories;

use App\Models\RoomTour;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RoomTour>
 */
class RoomTourFactory extends Factory
{
    protected $model = RoomTour::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tourDate = $this->faker->dateTimeBetween('-7 days', '+14 days');

        // Générer des assignations de chambres fictives
        $binomes = [];
        $binomeNames = ['Binôme A', 'Binôme B', 'Binôme C', 'Binôme D'];

        foreach ($binomeNames as $index => $name) {
            if ($index >= 2 && $this->faker->boolean(30)) {
                break;
            } // Parfois moins de binômes

            $roomCount = $this->faker->numberBetween(8, 15);
            $rooms = [];

            for ($i = 1; $i <= $roomCount; $i++) {
                $rooms[] = sprintf('%03d', $this->faker->unique()->numberBetween(100, 999));
            }

            $binomes[] = [
                'name' => $name,
                'member_ids' => $this->faker->numberBetween(2, 4), // Simulé pour la factory
                'assigned_rooms' => $rooms
            ];
        }

        return [
            'tour_date' => $tourDate->format('Y-m-d'),
            'is_active' => $this->faker->boolean(10), // 10% de chance d'être active
            'room_assignments' => $binomes,
        ];
    }

    /**
     * Indicate that the tour is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'tour_date' => now()->format('Y-m-d'),
        ]);
    }

    /**
     * Indicate that the tour is for today.
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'tour_date' => now()->format('Y-m-d'),
        ]);
    }

    /**
     * Indicate that the tour is in the future.
     */
    public function future(): static
    {
        return $this->state(fn (array $attributes) => [
            'tour_date' => $this->faker->dateTimeBetween('+1 day', '+30 days')->format('Y-m-d'),
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the tour is in the past.
     */
    public function past(): static
    {
        return $this->state(fn (array $attributes) => [
            'tour_date' => $this->faker->dateTimeBetween('-30 days', '-1 day')->format('Y-m-d'),
            'is_active' => false,
        ]);
    }
}
