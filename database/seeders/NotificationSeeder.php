<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::limit(10)->get();
        $admin = User::where('admin', true)->first();

        if ($users->isEmpty()) {
            $this->command->warn('Aucun utilisateur trouvé pour créer les notifications');
            return;
        }

        $globalNotifications = [
            [
                'title' => '🎿ienvenue sur SkiUT !',
                'description' => 'Votre application compagnon pour une semaine de ski inoubliable. Découvrez toutes les fonctionnalités disponibles !',
                'sender_id' => $admin?->id,
                'type' => 'global',
                'general' => true,
                'display' => true,
            ],
            [
                'title' => 'Planning de la semaine mis à jour',
                'description' => 'Le planning des activités a été mis à jour. Consultez-le pour ne rien manquer !',
                'sender_id' => $admin?->id,
                'type' => 'global',
                'general' => true,
                'display' => true,
            ],
            [
                'title' => 'Nouveaux défis disponibles',
                'description' => 'De nouveaux défis ont été ajoutés ! Relevez-les avec votre chambre pour gagner des points.',
                'sender_id' => $admin?->id,
                'type' => 'global',
                'general' => true,
                'display' => true,
            ],
            [
                'title' => 'Conditions météo exceptionnelles',
                'description' => 'Les conditions sont parfaites aujourd\'hui ! Profitez-en pour faire du ski et prendre de superbes photos.',
                'sender_id' => $admin?->id,
                'type' => 'global',
                'general' => true,
                'display' => true,
            ]
        ];

        foreach ($globalNotifications as $notifData) {
            $notification = Notification::create($notifData);

            foreach ($users as $user) {
                UserNotification::create([
                    'user_id' => $user->id,
                    'notification_id' => $notification->id,
                    'read' => fake()->boolean(30),
                    'read_at' => fake()->boolean(30) ? fake()->dateTimeBetween('-7 days', 'now') : null,
                ]);
            }
        }

        $targetedNotifications = [
            [
                'title' => 'Rappel de permanence',
                'description' => 'N\'oubliez pas votre permanence qui commence dans 1 heure. Merci pour votre aide !',
                'sender_id' => $admin?->id,
                'type' => 'targeted',
                'target_users' => $users->take(3)->pluck('id')->toArray(),
                'general' => false,
                'display' => true,
            ],
            [
                'title' => 'Défi spécial pour votre groupe',
                'description' => 'Un défi personnalisé a été créé spécialement pour votre groupe. Consultez la section défis !',
                'sender_id' => $admin?->id,
                'type' => 'targeted',
                'target_users' => $users->skip(2)->take(4)->pluck('id')->toArray(),
                'general' => false,
                'display' => true,
            ],
            [
                'title' => 'Excellent temps de glisse !',
                'description' => 'Félicitations ! Vous avez atteint une vitesse de pointe impressionnante lors de votre dernière session.',
                'sender_id' => $admin?->id,
                'type' => 'targeted',
                'target_users' => $users->take(2)->pluck('id')->toArray(),
                'general' => false,
                'display' => true,
            ]
        ];

        foreach ($targetedNotifications as $notifData) {
            $notification = Notification::create($notifData);

            foreach ($notifData['target_users'] as $userId) {
                UserNotification::create([
                    'user_id' => $userId,
                    'notification_id' => $notification->id,
                    'read' => fake()->boolean(20),
                    'read_at' => fake()->boolean(20) ? fake()->dateTimeBetween('-3 days', 'now') : null,
                ]);
            }
        }

        $chambres = ['101', '102', '201', '202', '301'];

        $roomNotifications = [
            [
                'title' => 'Tournée des chambres programmée',
                'description' => 'Une tournée des chambres aura lieu demain. Préparez-vous à accueillir le binôme responsable !',
                'sender_id' => $admin?->id,
                'type' => 'room_based',
                'target_rooms' => array_slice($chambres, 0, 3),
                'general' => false,
                'display' => true,
            ],
            [
                'title' => 'Nettoyage des espaces communs',
                'description' => 'Merci de maintenir la propreté des espaces communs. Votre collaboration est appréciée !',
                'sender_id' => $admin?->id,
                'type' => 'room_based',
                'target_rooms' => $chambres,
                'general' => false,
                'display' => true,
            ]
        ];

        foreach ($roomNotifications as $notifData) {
            $notification = Notification::create($notifData);

            foreach ($notifData['target_rooms'] as $roomId) {
                $roomUsers = User::where('room_id', $roomId)->get();
                foreach ($roomUsers as $user) {
                    UserNotification::create([
                        'user_id' => $user->id,
                        'notification_id' => $notification->id,
                        'read' => fake()->boolean(40),
                        'read_at' => fake()->boolean(40) ? fake()->dateTimeBetween('-2 days', 'now') : null,
                    ]);
                }
            }
        }

        $skinderNotifications = [
            [
                'title' => 'Nouveau match Skinder !',
                'description' => 'Les chambres 101 et 205 ont matché ! C\'est le moment de faire connaissance et de se rencontrer. Bonne chance ! 🎉',
                'sender_id' => null,
                'type' => 'targeted',
                'target_users' => $users->take(4)->pluck('id')->toArray(),
                'general' => false,
                'display' => true,
            ],
            [
                'title' => 'Nouveau match Skinder !',
                'description' => 'Les chambres 302 et 108 ont matché ! C\'est le moment de faire connaissance et de se rencontrer. Bonne chance ! 🎉',
                'sender_id' => null,
                'type' => 'targeted',
                'target_users' => $users->skip(4)->take(3)->pluck('id')->toArray(),
                'general' => false,
                'display' => true,
            ]
        ];

        foreach ($skinderNotifications as $notifData) {
            $notification = Notification::create($notifData);

            foreach ($notifData['target_users'] as $userId) {
                UserNotification::create([
                    'user_id' => $userId,
                    'notification_id' => $notification->id,
                    'read' => fake()->boolean(60),
                    'read_at' => fake()->boolean(60) ? fake()->dateTimeBetween('-1 day', 'now') : null,
                ]);
            }
        }
    }
}
