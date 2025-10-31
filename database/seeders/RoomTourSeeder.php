<?php

namespace Database\Seeders;

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

        // Générer une liste de chambres fictives
        $allRooms = [];
        for ($floor = 1; $floor <= 4; $floor++) {
            for ($room = 1; $room <= 25; $room++) {
                $allRooms[] = sprintf('%d%02d', $floor, $room);
            }
        }

        // Mélanger et limiter les chambres
        $allRooms = fake()->randomElements($allRooms, 60);

        // 1. Créer une tournée pour aujourd'hui (active)
        $todayTour = $this->createTour(now()->format('Y-m-d'), true, $members, $allRooms);

        // 2. Créer une tournée pour demain
        $tomorrowTour = $this->createTour(now()->addDay()->format('Y-m-d'), false, $members, $allRooms);

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

    private function createTour(string $date, bool $isActive, $members, array $allRooms, bool $isPast = false): RoomTour
    {
        // Créer la tournée
        $tour = RoomTour::create([
            'tour_date' => $date,
            'is_active' => $isActive,
            'room_assignments' => [], // Sera rempli après création des binômes
        ]);

        // Définir le nombre de binômes (2-4)
        $binomeCount = fake()->numberBetween(2, 4);
        $binomeNames = ['Binôme A', 'Binôme B', 'Binôme C', 'Binôme D'];

        $assignments = [];
        $usedMembers = [];
        $remainingRooms = $allRooms;

        for ($i = 0; $i < $binomeCount; $i++) {
            // Sélectionner les membres pour ce binôme (2-3 membres)
            $binomeMemberCount = fake()->numberBetween(2, 3);
            $availableMembers = $members->whereNotIn('id', $usedMembers);

            if ($availableMembers->count() < $binomeMemberCount) {
                break; // Plus assez de membres
            }

            $binomeMembers = $availableMembers->random(min($binomeMemberCount, $availableMembers->count()));
            $memberIds = $binomeMembers->pluck('id')->toArray();
            $usedMembers = array_merge($usedMembers, $memberIds);

            // Assigner des chambres à ce binôme
            $roomsPerBinome = intval(count($remainingRooms) / ($binomeCount - $i));
            $roomsPerBinome = max(8, min(20, $roomsPerBinome)); // Entre 8 et 20 chambres

            $assignedRooms = array_splice($remainingRooms, 0, $roomsPerBinome);

            // Créer le binôme
            $binome = TourBinome::create([
                'room_tour_id' => $tour->id,
                'binome_name' => $binomeNames[$i],
                'member_ids' => $memberIds,
                'assigned_rooms' => $assignedRooms,
                'visited_rooms' => [],
            ]);

            // Créer les visites pour chaque chambre
            foreach ($assignedRooms as $index => $roomId) {
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
                elseif ($isActive && $index < count($assignedRooms) * 0.4) {
                    $visited = fake()->boolean(60);
                    if ($visited) {
                        $visitedAt = fake()->dateTimeBetween('today 09:00', 'now');
                        $notes = fake()->optional(0.2)->sentence();
                    }
                }

                RoomTourVisit::create([
                    'tour_binome_id' => $binome->id,
                    'room_id' => $roomId,
                    'visited' => $visited,
                    'visited_at' => $visitedAt,
                    'visit_order' => $index + 1,
                    'notes' => $notes,
                ]);
            }

            // Mettre à jour visited_rooms si nécessaire
            if ($visited ?? false) {
                $visitedRooms = RoomTourVisit::where('tour_binome_id', $binome->id)
                    ->where('visited', true)
                    ->pluck('room_id')
                    ->toArray();

                $binome->update(['visited_rooms' => $visitedRooms]);
            }

            // Préparer les données d'assignment
            $assignments[] = [
                'name' => $binomeNames[$i],
                'member_ids' => $memberIds,
                'assigned_rooms' => $assignedRooms,
            ];
        }

        // Mettre à jour les room_assignments de la tournée
        $tour->update(['room_assignments' => $assignments]);

        return $tour;
    }
}
