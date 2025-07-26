<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Room;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rooms = Room::all();
        
        for ($i = 0; $i < 30; $i++) {
            User::factory()->create([
                'roomID' => $rooms->random()->id
            ]);
        }
    }
} 