<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $rooms = Room::all();

        for ($i = 0; $i < 30; $i++) {
            User::factory()->create([
                'room_id' => $rooms->random()->id
            ]);
        }
    }
}
