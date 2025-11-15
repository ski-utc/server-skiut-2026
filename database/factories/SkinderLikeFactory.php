<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SkinderLikeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $index = 0;
        $maxRooms = 15;

        $liker_id = ($index % $maxRooms) + 1;
        $liked_id = (int)($index / $maxRooms) + 1;

        if ($liked_id === $liker_id) {
            $liked_id = ($liked_id % $maxRooms) + 1;
            if ($liked_id === $liker_id) {
                $liked_id = $liked_id === $maxRooms ? 1 : $maxRooms;
            }
        }

        if ($liked_id > $maxRooms) {
            $liked_id = fake()->numberBetween(1, $maxRooms);
            if ($liked_id === $liker_id) {
                $liked_id = $liked_id === $maxRooms ? 1 : $maxRooms;
            }
        }

        $index++;

        return [
            'room_liker_id' => $liker_id,
            'room_liked_id' => $liked_id,
        ];
    }
}
