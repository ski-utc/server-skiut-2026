<?php

namespace Database\Seeders;

use App\Models\Shotguns;
use Illuminate\Database\Seeder;

class ShotgunsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Shotguns::factory(50)->create();
    }
}
