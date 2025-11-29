<?php

namespace Database\Seeders;

use App\Models\RoomShotgun;
use Illuminate\Database\Seeder;

class RoomShotgunSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        RoomShotgun::factory()->count(10)->create();
    }
}
