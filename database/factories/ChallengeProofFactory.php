<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ChallengeProofFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fileExtensions = ['jpg', 'png', 'mp4', 'mov', 'avi'];
        $mediaType = fake()->randomElement(['image', 'video']);

        $extension = $mediaType === 'image'
            ? fake()->randomElement(['jpg', 'png'])
            : fake()->randomElement(['mp4', 'mov', 'avi']);

        return [
            'file' => 'proofs/' . fake()->uuid() . '.' . $extension,
            'media_type' => $mediaType,
            'nb_likes' => fake()->numberBetween(0, 50),
            'valid' => fake()->boolean(75),
            'alert' => fake()->numberBetween(0, 5),
            'delete' => fake()->boolean(5),
            'challenge_id' => fake()->numberBetween(1, 15),
            'room_id' => fake()->numberBetween(1, 15),
            'user_id' => fake()->numberBetween(1, 30),
        ];
    }
}
