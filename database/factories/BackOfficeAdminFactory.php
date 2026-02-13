<?php

namespace Database\Factories;

use App\Models\BackOfficeAdmin;
use Illuminate\Database\Eloquent\Factories\Factory;

class BackOfficeAdminFactory extends Factory
{
    protected $model = BackOfficeAdmin::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => $this->faker->unique()->email(),
        ];
    }
}
