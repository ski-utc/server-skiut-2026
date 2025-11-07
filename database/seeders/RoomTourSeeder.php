<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\RoomTour;
use App\Models\RoomTourVisit;
use App\Models\TourBinome;
use App\Models\User;
use Illuminate\Database\Seeder;

class RoomTourSeeder extends Seeder
{
    public function run(): void
    {
        $members = User::where('member', true)->get();

        if ($members->count() < 4) {
            $users = User::limit(8)->get();
            foreach ($users as $user) {
                $user->update(['member' => true]);
            }
            $members = User::where('member', true)->get();
        }

        if ($members->isEmpty()) {
            $this->command->warn('Aucun membre trouvé pour créer les tournées');
            return;
        }

        $allRooms = Room::all();

        if ($allRooms->isEmpty()) {
            $this->command->warn('Aucune chambre trouvée pour créer les tournées');
            return;
        }

        $this->createTour(now()->format('Y-m-d'), true, $members, $allRooms);

        $this->createTour(now()->addDay()->format('Y-m-d'), false, $members, $allRooms);

        for ($i = 1; $i <= 5; $i++) {
            $pastDate = now()->subDays($i)->format('Y-m-d');
            $this->createTour($pastDate, false, $members, $allRooms, true);
        }

        for ($i = 2; $i <= 7; $i++) {
            $futureDate = now()->addDays($i)->format('Y-m-d');
            $this->createTour($futureDate, false, $members, $allRooms);
        }
    }

    private function createTour(string $date, bool $isActive, $members, $allRooms, bool $isPast = false): RoomTour
    {
        $tour = RoomTour::create([
            'tour_date' => $date,
            'is_active' => $isActive
        ]);

        $binomeCount = min(fake()->numberBetween(2, 4), floor($members->count() / 2));
        $binomeNames = ['Binôme A', 'Binôme B', 'Binôme C', 'Binôme D'];

        $usedMembers = [];
        $availableRooms = $allRooms->shuffle();
        $roomIndex = 0;

        for ($i = 0; $i < $binomeCount; $i++) {
            $availableMembers = $members->whereNotIn('id', $usedMembers);

            if ($availableMembers->count() < 2) {
                break;
            }

            $binomeMembers = $availableMembers->random(2);
            $member1 = $binomeMembers[0];
            $member2 = $binomeMembers[1];

            $usedMembers[] = $member1->id;
            $usedMembers[] = $member2->id;

            $binome = TourBinome::create([
                'room_tour_id' => $tour->id,
                'binome_name' => $binomeNames[$i],
                'member_1_id' => $member1->id,
                'member_2_id' => $member2->id
            ]);

            $roomsPerBinome = min(fake()->numberBetween(5, 8), $availableRooms->count() - $roomIndex);
            $assignedRooms = $availableRooms->slice($roomIndex, $roomsPerBinome);
            $roomIndex += $roomsPerBinome;

            foreach ($assignedRooms as $index => $room) {
                $visited = false;
                $visitedAt = null;

                if ($isPast) {
                    $visited = fake()->boolean(85);
                    if ($visited) {
                        $visitedAt = fake()->dateTimeBetween($date . ' 09:00', $date . ' 17:00');
                    }
                } elseif ($isActive && $index < $assignedRooms->count() * 0.4) {
                    $visited = fake()->boolean(60);
                    if ($visited) {
                        $visitedAt = fake()->dateTimeBetween('today 09:00', 'now');
                    }
                }

                RoomTourVisit::create([
                    'tour_binome_id' => $binome->id,
                    'room_id' => $room->id,
                    'visited' => $visited,
                    'visited_at' => $visitedAt,
                    'visit_order' => $index + 1,
                ]);
            }
        }

        return $tour;
    }
}
