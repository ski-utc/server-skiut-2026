<?php

namespace Database\Seeders;

use app\Models\User;
use Database\Seeders\ChallengesSeeder; // Add this line to import ChallengesSeeder
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call the ChallengesSeeder
        $this->call(ChallengesSeeder::class); // Add this line to run the ChallengesSeeder
    }
}
