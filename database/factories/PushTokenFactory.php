<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PushTokenFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'token' => 'ExponentPushToken[' . $this->faker->bothify('??????????????????') . ']',
            'device_type' => $this->faker->randomElement(['ios', 'android']),
            'device_name' => $this->faker->optional()->randomElement([
                'iPhone 14 Pro',
                'iPhone 13',
                'Samsung Galaxy S23',
                'Google Pixel 7',
                'OnePlus 11',
            ]),
            'active' => $this->faker->boolean(90),
            'last_used_at' => $this->faker->optional(0.7)->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
