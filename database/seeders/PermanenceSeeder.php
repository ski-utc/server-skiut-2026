<?php

namespace Database\Seeders;

use App\Models\Permanence;
use App\Models\User;
use Illuminate\Database\Seeder;

class PermanenceSeeder extends Seeder
{
    public function run(): void
    {   
        $members = User::where('member', true)->get();

        if ($members->isEmpty()) {
            $users = User::limit(5)->get();
            foreach ($users as $user) {
                $user->update(['member' => true]);
            }
            $members = $users;
        }

        if ($members->isEmpty()) {
            $this->command->warn('Aucun membre trouvé pour créer les permanences');
            return;
        }

        $permanenceTypes = [
            [
                'name' => 'Accueil voyageurs',
                'description' => 'Accueillir les nouveaux arrivants, leur expliquer les règles et leur remettre les clés.',
                'location' => 'Hall d\'accueil',
                'duration' => 2,
            ],
            [
                'name' => 'Maintenance matériel',
                'description' => 'Vérifier l\'état du matériel de ski, effectuer les réparations mineures et la maintenance préventive.',
                'location' => 'Local matériel',
                'duration' => 3,
            ],
            [
                'name' => 'Gestion logistique',
                'description' => 'Coordination des livraisons, gestion des stocks et organisation des espaces communs.',
                'location' => 'Bureau organisation',
                'duration' => 2,
            ],
            [
                'name' => 'Animation soirée',
                'description' => 'Préparer et animer les soirées thématiques, jeux et activités de groupe.',
                'location' => 'Salle commune',
                'duration' => 4,
            ],
            [
                'name' => 'Nettoyage espaces communs',
                'description' => 'Maintenir la propreté des cuisines, salles communes et espaces de détente.',
                'location' => 'Espaces communs',
                'duration' => 2,
            ],
            [
                'name' => 'Préparation repas',
                'description' => 'Aider à la préparation des repas collectifs et gérer la cuisine commune.',
                'location' => 'Cuisine',
                'duration' => 3,
            ],
            [
                'name' => 'Surveillance matériel',
                'description' => 'S\'assurer de la sécurité du matériel et aider les autres avec les équipements.',
                'location' => 'Zone stockage',
                'duration' => 2,
            ],
            [
                'name' => 'Support technique',
                'description' => 'Aider avec les problèmes techniques, WiFi, applications et équipements électroniques.',
                'location' => 'Local technique',
                'duration' => 2,
            ],
            [
                'name' => 'Coordination transport',
                'description' => 'Organiser les navettes, coordonner les déplacements et gérer les plannings de transport.',
                'location' => 'Point de rendez-vous',
                'duration' => 2,
            ],
            [
                'name' => 'Première aide',
                'description' => 'Assurer une présence formée aux premiers secours et gérer la trousse de secours.',
                'location' => 'Infirmerie',
                'duration' => 4,
            ]
        ];

        $startDate = now();
        $endDate = now()->addDays(14);

        $createdPermanences = 0;

        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dailyPermanences = fake()->numberBetween(2, 5);

            for ($i = 0; $i < $dailyPermanences; $i++) {
                $permanenceType = fake()->randomElement($permanenceTypes);

                $timeSlots = [
                    ['start' => 8, 'label' => 'Matin'],
                    ['start' => 10, 'label' => 'Milieu de matinée'],
                    ['start' => 14, 'label' => 'Après-midi'],
                    ['start' => 16, 'label' => 'Fin d\'après-midi'],
                    ['start' => 19, 'label' => 'Soirée'],
                ];

                $timeSlot = fake()->randomElement($timeSlots);
                $startHour = $timeSlot['start'];
                $duration = $permanenceType['duration'];

                $startDateTime = $date->copy()->setTime($startHour, 0);
                $endDateTime = $startDateTime->copy()->addHours($duration);

                $availableMembers = $members->filter(function ($member) use ($startDateTime, $endDateTime) {
                    $conflicts = Permanence::where('responsible_user_id', $member->id)
                        ->where(function ($query) use ($startDateTime, $endDateTime) {
                            $query->whereBetween('start_datetime', [$startDateTime, $endDateTime])
                                  ->orWhereBetween('end_datetime', [$startDateTime, $endDateTime])
                                  ->orWhere(function ($q) use ($startDateTime, $endDateTime) {
                                      $q->where('start_datetime', '<=', $startDateTime)
                                        ->where('end_datetime', '>=', $endDateTime);
                                  });
                        })
                        ->exists();

                    return !$conflicts;
                });

                if ($availableMembers->isEmpty()) {
                    continue;
                }

                $responsibleMember = $availableMembers->random();

                $status = 'scheduled';
                $notificationSent = false;

                if ($startDateTime->isPast()) {
                    if ($endDateTime->isPast()) {
                        $status = fake()->randomElement(['completed', 'completed', 'completed', 'cancelled']);
                        $notificationSent = true;
                    } else {
                        $status = 'in_progress';
                        $notificationSent = true;
                    }
                } elseif ($startDateTime->diffInHours(now()) <= 2) {
                    $notificationSent = fake()->boolean(80);
                }

                $permanence = Permanence::create([
                    'name' => $permanenceType['name'],
                    'description' => $permanenceType['description'],
                    'start_datetime' => $startDateTime,
                    'end_datetime' => $endDateTime,
                    'location' => $permanenceType['location'],
                    'status' => $status,
                    'responsible_user_id' => $responsibleMember->id,
                    'notes' => $status === 'completed' ? fake()->optional(0.4)->paragraph() : null,
                    'notification_sent' => $notificationSent,
                ]);

                $createdPermanences++;
            }
        }

        for ($i = 0; $i < 3; $i++) {
            $permanenceType = fake()->randomElement($permanenceTypes);
            $startDateTime = now()->addHours(fake()->numberBetween(1, 6));
            $endDateTime = $startDateTime->copy()->addHours($permanenceType['duration']);

            Permanence::create([
                'name' => $permanenceType['name'] . ' (Urgent)',
                'description' => 'URGENT - ' . $permanenceType['description'] . ' Merci de vous présenter à l\'heure !',
                'start_datetime' => $startDateTime,
                'end_datetime' => $endDateTime,
                'location' => $permanenceType['location'],
                'status' => 'scheduled',
                'responsible_user_id' => $members->random()->id,
                'notes' => null,
                'notification_sent' => false,
            ]);

            $createdPermanences++;
        }
    }
}
