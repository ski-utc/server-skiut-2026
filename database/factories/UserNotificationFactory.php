<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserNotificationFactory extends Factory
{
    protected $model = UserNotification::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'notification_id' => Notification::factory(),
            'read' => $this->faker->boolean(30),
            'read_at' => function (array $attributes) {
                return $attributes['read'] ? $this->faker->dateTimeBetween('-7 days', 'now') : null;
            },
        ];
    }

    /**
     * Define the model's read state.
     *
     * @return static
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'read' => true,
            'read_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /**
     * Define the model's unread state.
     *
     * @return static
     */
    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'read' => false,
            'read_at' => null,
        ]);
    }
}
