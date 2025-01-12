<?php

Namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserPerformance;
use App\Models\User; 
use Faker\Factory as Faker;

class UserPerformanceSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        // Fetch all existing user IDs
        $user_ids = User::pluck('id')->toArray();

        // Insert 10 random values with existing user IDs
        foreach (range(1, 10) as $index) {
            UserPerformance::create([
                'user_id' => $faker->randomElement($user_ids),  // Randomly select an existing user_id
                'max_speed' => $faker->randomFloat(2, 20, 100),  // Random max_speed between 20 and 100
                'total_distance' => $faker->randomFloat(2, 1, 50),  // Random total_distance between 1 and 50
            ]);
        }
    }
}

