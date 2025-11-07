<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\Transport;
use App\Models\User;
use Illuminate\Database\Seeder;

class RelationsSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $rooms = Room::all();

        foreach ($rooms as $room) {
            $room->update(['user_id' => $users->random()->id]);
        }

        $transports = Transport::all();

        if ($users->isNotEmpty() && $transports->isNotEmpty()) {
            foreach ($users as $user) {
                $randomTransports = $transports->random(rand(1, min(3, $transports->count())));
                $user->transports()->attach($randomTransports->pluck('id')->toArray());
            }
        }
    }
}
