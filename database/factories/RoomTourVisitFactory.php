<?php

namespace Database\Factories;

use App\Models\RoomTourVisit;
use App\Models\TourBinome;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomTourVisitFactory extends Factory
{
    protected $model = RoomTourVisit::class;

    public function definition(): array
    {
        $visited = $this->faker->boolean(40);

        return [
            'tour_binome_id' => TourBinome::factory(),
            'room_id' => sprintf('%03d', $this->faker->numberBetween(100, 999)),
            'visited' => $visited,
            'visited_at' => $visited ? $this->faker->dateTimeBetween('-1 day', 'now') : null,
            'visit_order' => $this->faker->numberBetween(1, 20),
        ];
    }

    public function visited(): static
    {
        return $this->state(fn (array $attributes) => [
            'visited' => true,
            'visited_at' => $this->faker->dateTimeBetween('-1 day', 'now'),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'visited' => false,
            'visited_at' => null,
        ]);
    }

    public function forRoom(string $roomId): static
    {
        return $this->state(fn (array $attributes) => [
            'room_id' => $roomId,
        ]);
    }

    public function withOrder(int $order): static
    {
        return $this->state(fn (array $attributes) => [
            'visit_order' => $order,
        ]);
    }

    public function recentlyVisited(): static
    {
        return $this->state(fn (array $attributes) => [
            'visited' => true,
            'visited_at' => $this->faker->dateTimeBetween('-2 hours', 'now'),
        ]);
    }
}
