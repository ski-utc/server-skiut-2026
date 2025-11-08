<?php

namespace Database\Seeders;

use App\Models\UserRoomShotgun;
use Illuminate\Database\Seeder;

class UserRoomShotgunSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UserRoomShotgun::factory(30)->create();
    }
}
