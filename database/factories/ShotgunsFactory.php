<?php

namespace Database\Factories;

use App\Models\Shotguns;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShotgunsFactory extends Factory
{
    protected $model = Shotguns::class;

    public function definition(): array
    {
        return [
            'email' => $this->faker->unique()->email(),
        ];
    }
}
