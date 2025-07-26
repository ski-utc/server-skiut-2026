<?php

namespace Database\Seeders;

use App\Models\UserPerformance;
use Illuminate\Database\Seeder;

class UserPerformanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UserPerformance::factory(30)->create();
    }
} 