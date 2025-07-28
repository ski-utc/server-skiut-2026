<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;

class RelationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Assigner des responsables aux rooms
        $users = User::all();
        $rooms = Room::all();

        foreach ($rooms as $room) {
            $room->update(['userID' => $users->random()->id]);
        }
    }
}
