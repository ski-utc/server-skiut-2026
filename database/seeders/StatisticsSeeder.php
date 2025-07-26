<?php

namespace Database\Seeders;

use App\Models\Statistics;
use Illuminate\Database\Seeder;

class StatisticsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Statistics::factory(60)->create();
    }
} 