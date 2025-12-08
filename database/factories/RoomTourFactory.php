<?php

namespace Database\Factories;

use App\Models\RoomTour;
use Illuminate\Database\Eloquent\Factories\Factory;

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

        return [
            'tour_date' => $tourDate->format('Y-m-d'),
            'is_active' => $this->faker->boolean(10)
        ];
    }

    /**
     * Define the model's active state.
     *
     * @return static
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'tour_date' => now()->format('Y-m-d'),
        ]);
    }

    /**
     * Define the model's today state.
     *
     * @return static
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'tour_date' => now()->format('Y-m-d'),
        ]);
    }

    /**
     * Define the model's future state.
     *
     * @return static
     */
    public function future(): static
    {
        return $this->state(fn (array $attributes) => [
            'tour_date' => $this->faker->dateTimeBetween('+1 day', '+30 days')->format('Y-m-d'),
            'is_active' => false,
        ]);
    }

    /**
     * Define the model's past state.
     *
     * @return static
     */
    public function past(): static
    {
        return $this->state(fn (array $attributes) => [
            'tour_date' => $this->faker->dateTimeBetween('-30 days', '-1 day')->format('Y-m-d'),
            'is_active' => false,
        ]);
    }
}
