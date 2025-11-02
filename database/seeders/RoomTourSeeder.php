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
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer les membres de l'association
        $members = User::where('member', true)->get();

        if ($members->count() < 4) {
            // Créer quelques membres si pas assez
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

        // Récupérer toutes les chambres existantes
        $allRooms = Room::all();

        if ($allRooms->isEmpty()) {
            $this->command->warn('Aucune chambre trouvée pour créer les tournées');
            return;
        }

        // 1. Créer une tournée pour aujourd'hui (active)
        $this->createTour(now()->format('Y-m-d'), true, $members, $allRooms);

        // 2. Créer une tournée pour demain
        $this->createTour(now()->addDay()->format('Y-m-d'), false, $members, $allRooms);

        // 3. Créer quelques tournées passées
        for ($i = 1; $i <= 5; $i++) {
            $pastDate = now()->subDays($i)->format('Y-m-d');
            $this->createTour($pastDate, false, $members, $allRooms, true);
        }

        // 4. Créer quelques tournées futures
        for ($i = 2; $i <= 7; $i++) {
            $futureDate = now()->addDays($i)->format('Y-m-d');
            $this->createTour($futureDate, false, $members, $allRooms);
        }

        $this->command->info('Tournées de chambres créées avec succès !');
    }

    private function createTour(string $date, bool $isActive, $members, $allRooms, bool $isPast = false): RoomTour
    {
        // Créer la tournée
        $tour = RoomTour::create([
            'tour_date' => $date,
            'is_active' => $isActive
        ]);

        // Définir le nombre de binômes (2-4)
        $binomeCount = min(fake()->numberBetween(2, 4), floor($members->count() / 2));
        $binomeNames = ['Binôme A', 'Binôme B', 'Binôme C', 'Binôme D'];

        $usedMembers = [];
        $availableRooms = $allRooms->shuffle();
        $roomIndex = 0;

        for ($i = 0; $i < $binomeCount; $i++) {
            // Sélectionner EXACTEMENT 2 membres pour ce binôme
            $availableMembers = $members->whereNotIn('id', $usedMembers);

            if ($availableMembers->count() < 2) {
                break; // Plus assez de membres
            }

            $binomeMembers = $availableMembers->random(2);
            $member1 = $binomeMembers[0];
            $member2 = $binomeMembers[1];
            
            $usedMembers[] = $member1->id;
            $usedMembers[] = $member2->id;

            // Créer le binôme
            $binome = TourBinome::create([
                'room_tour_id' => $tour->id,
                'binome_name' => $binomeNames[$i],
                'member_1_id' => $member1->id,
                'member_2_id' => $member2->id
            ]);

            // Assigner 5-8 chambres à ce binôme
            $roomsPerBinome = min(fake()->numberBetween(5, 8), $availableRooms->count() - $roomIndex);
            $assignedRooms = $availableRooms->slice($roomIndex, $roomsPerBinome);
            $roomIndex += $roomsPerBinome;

            // Créer les visites pour chaque chambre
            foreach ($assignedRooms as $index => $room) {
                $visited = false;
                $visitedAt = null;
                $notes = null;

                // Si c'est une tournée passée, simuler des visites
                if ($isPast) {
                    $visited = fake()->boolean(85); // 85% des chambres visitées
                    if ($visited) {
                        $visitedAt = fake()->dateTimeBetween($date . ' 09:00', $date . ' 17:00');
                        $notes = fake()->optional(0.3)->sentence();
                    }
                }
                // Si c'est la tournée active d'aujourd'hui, simuler quelques visites
                elseif ($isActive && $index < $assignedRooms->count() * 0.4) {
                    $visited = fake()->boolean(60);
                    if ($visited) {
                        $visitedAt = fake()->dateTimeBetween('today 09:00', 'now');
                        $notes = fake()->optional(0.2)->sentence();
                    }
                }

                RoomTourVisit::create([
                    'tour_binome_id' => $binome->id,
                    'room_id' => $room->id, // Utiliser l'ID de la room, pas le roomNumber
                    'visited' => $visited,
                    'visited_at' => $visitedAt,
                    'visit_order' => $index + 1,
                    'notes' => $notes
                ]);
            }
        }

        return $tour;
    }
}
