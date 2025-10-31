<?php

namespace Database\Factories;

use App\Models\RoomTour;
use App\Models\TourBinome;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TourBinome>
 */
class TourBinomeFactory extends Factory
{
    protected $model = TourBinome::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $binomeNames = ['Binôme A', 'Binôme B', 'Binôme C', 'Binôme D', 'Équipe Alpha', 'Équipe Beta'];

        // Générer une liste de chambres
        $roomCount = $this->faker->numberBetween(8, 20);
        $assignedRooms = [];

        for ($i = 0; $i < $roomCount; $i++) {
            $assignedRooms[] = sprintf('%03d', $this->faker->unique()->numberBetween(100, 999));
        }

        // Simuler quelques chambres déjà visitées
        $visitedCount = $this->faker->numberBetween(0, min($roomCount - 1, 5));
        $visitedRooms = array_slice($assignedRooms, 0, $visitedCount);

        // Générer les IDs des membres (on utilise des IDs factices pour la démo)
        $memberIds = [];
        $memberCount = $this->faker->numberBetween(2, 4);

        for ($i = 0; $i < $memberCount; $i++) {
            $memberIds[] = $this->faker->numberBetween(1, 100); // IDs factices
        }

        return [
            'room_tour_id' => RoomTour::factory(),
            'binome_name' => $this->faker->randomElement($binomeNames),
            'member_ids' => $memberIds,
            'assigned_rooms' => $assignedRooms,
            'visited_rooms' => $visitedRooms,
        ];
    }

    /**
     * Indicate that this binome has completed their tour.
     */
    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            $assignedRooms = $attributes['assigned_rooms'] ?? [];

            return [
                'visited_rooms' => $assignedRooms, // Toutes les chambres visitées
            ];
        });
    }

    /**
     * Indicate that this binome just started.
     */
    public function justStarted(): static
    {
        return $this->state(fn (array $attributes) => [
            'visited_rooms' => [], // Aucune chambre visitée
        ]);
    }

    /**
     * Indicate that this binome is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(function (array $attributes) {
            $assignedRooms = $attributes['assigned_rooms'] ?? [];
            $totalRooms = count($assignedRooms);

            if ($totalRooms === 0) {
                return ['visited_rooms' => []];
            }

            // Visiter entre 30% et 70% des chambres
            $visitedCount = $this->faker->numberBetween(
                max(1, intval($totalRooms * 0.3)),
                intval($totalRooms * 0.7)
            );

            return [
                'visited_rooms' => array_slice($assignedRooms, 0, $visitedCount),
            ];
        });
    }

    /**
     * Create a binome with specific members.
     */
    public function withMembers(array $userIds): static
    {
        return $this->state(fn (array $attributes) => [
            'member_ids' => $userIds,
        ]);
    }

    /**
     * Create a binome with specific rooms.
     */
    public function withRooms(array $rooms): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_rooms' => $rooms,
            'visited_rooms' => [], // Reset visited rooms
        ]);
    }
}
