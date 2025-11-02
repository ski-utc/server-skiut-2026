<?php

namespace Database\Factories;

use App\Models\RoomTour;
use App\Models\TourBinome;
use App\Models\User;
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

        return [
            'room_tour_id' => RoomTour::factory(),
            'binome_name' => $this->faker->randomElement($binomeNames),
            'member_1_id' => User::factory(),
            'member_2_id' => User::factory()
        ];
    }

    /**
     * Create a binome with specific members.
     */
    public function withMembers(int $member1Id, int $member2Id): static
    {
        return $this->state(fn (array $attributes) => [
            'member_1_id' => $member1Id,
            'member_2_id' => $member2Id
        ]);
    }
}
