<?php

namespace Database\Factories;

use App\Models\RoomTourVisit;
use App\Models\TourBinome;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RoomTourVisit>
 */
class RoomTourVisitFactory extends Factory
{
    protected $model = RoomTourVisit::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $visited = $this->faker->boolean(40); // 40% de chance d'être visitée

        return [
            'tour_binome_id' => TourBinome::factory(),
            'room_id' => sprintf('%03d', $this->faker->numberBetween(100, 999)),
            'visited' => $visited,
            'visited_at' => $visited ? $this->faker->dateTimeBetween('-1 day', 'now') : null,
            'visit_order' => $this->faker->numberBetween(1, 20),
            'notes' => $this->faker->optional(0.3)->sentence(),
        ];
    }

    /**
     * Indicate that the room has been visited.
     */
    public function visited(): static
    {
        return $this->state(fn (array $attributes) => [
            'visited' => true,
            'visited_at' => $this->faker->dateTimeBetween('-1 day', 'now'),
            'notes' => $this->faker->optional(0.6)->sentence(),
        ]);
    }

    /**
     * Indicate that the room has not been visited yet.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'visited' => false,
            'visited_at' => null,
            'notes' => null,
        ]);
    }

    /**
     * Indicate that the room was visited with detailed notes.
     */
    public function withNotes(): static
    {
        return $this->state(fn (array $attributes) => [
            'visited' => true,
            'visited_at' => $this->faker->dateTimeBetween('-1 day', 'now'),
            'notes' => $this->faker->paragraph(),
        ]);
    }

    /**
     * Create a visit for a specific room.
     */
    public function forRoom(string $roomId): static
    {
        return $this->state(fn (array $attributes) => [
            'room_id' => $roomId,
        ]);
    }

    /**
     * Create a visit with a specific order.
     */
    public function withOrder(int $order): static
    {
        return $this->state(fn (array $attributes) => [
            'visit_order' => $order,
        ]);
    }

    /**
     * Create a visit that was completed recently.
     */
    public function recentlyVisited(): static
    {
        return $this->state(fn (array $attributes) => [
            'visited' => true,
            'visited_at' => $this->faker->dateTimeBetween('-2 hours', 'now'),
            'notes' => $this->faker->optional(0.5)->sentence(),
        ]);
    }
}
