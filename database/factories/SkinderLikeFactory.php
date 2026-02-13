<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SkinderLikeFactory extends Factory
{
    /**
     * Track generated pairs to avoid duplicates
     */
    private static array $generatedPairs = [];
    private static int $maxAttempts = 100;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $maxRooms = 15;
        $attempts = 0;

        do {
            $liker_id = fake()->numberBetween(1, $maxRooms);
            $liked_id = fake()->numberBetween(1, $maxRooms);

            while ($liked_id === $liker_id) {
                $liked_id = fake()->numberBetween(1, $maxRooms);
            }

            $pairKey = "{$liker_id}-{$liked_id}";
            $attempts++;

            if (!isset(self::$generatedPairs[$pairKey])) {
                self::$generatedPairs[$pairKey] = true;
                break;
            }

            if ($attempts >= self::$maxAttempts) {
                self::$generatedPairs = [];
                $pairKey = "{$liker_id}-{$liked_id}";
                self::$generatedPairs[$pairKey] = true;
                break;
            }
        } while (true);

        return [
            'room_liker_id' => $liker_id,
            'room_liked_id' => $liked_id,
        ];
    }
}
