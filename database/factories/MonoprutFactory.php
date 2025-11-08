<?php

namespace Database\Factories;

use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

class MonoprutFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['fruit', 'veggie', 'drink', 'sweet', 'snack', 'dairy', 'bread', 'meat', 'fish', 'grain', 'other'];

        $hasReceiver = $this->faker->boolean(70);

        return [
            'product' => $this->faker->word(),
            'quantity' => (string) $this->faker->numberBetween(1, 50),
            'type' => $this->faker->randomElement($types),
            'giver_room_id' => Room::factory(),
            'receiver_room_id' => $hasReceiver ? null : null,
        ];
    }

    /**
     * Define the model's fruit state.
     *
     * @return static
     */
    public function fruit(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'fruit',
            'product' => $this->faker->randomElement(['apple', 'banana', 'orange', 'strawberry']),
        ]);
    }

    /**
     * Define the model's drink state.
     *
     * @return static
     */
    public function drink(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'drink',
            'product' => $this->faker->randomElement(['water', 'soda', 'juice', 'coffee']),
        ]);
    }

    /**
     * Define the model's with receiver state.
     *
     * @return static
     */
    public function withReceiver(): static
    {
        return $this->afterMaking(function ($monoprut) {
            $rooms = Room::pluck('id')->toArray();
            if (!empty($rooms)) {
                $monoprut->receiver_room_id = $this->faker->randomElement($rooms);
            }
        });
    }

    /**
     * Define the model's without receiver state.
     *
     * @return static
     */
    public function withoutReceiver(): static
    {
        return $this->state(fn (array $attributes) => [
            'receiver_room_id' => null,
        ]);
    }
}
