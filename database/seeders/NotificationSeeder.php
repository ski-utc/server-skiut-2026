<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notification;

class NotificationSeeder extends Seeder
{
    public function run()
    {
        Notification::truncate();
        $notifications = [
            [ 
                'title' => 'Nouvelle tâche assignée',
                'description' => 'Une nouvelle tâche vous a été assignée. Veuillez la consulter dans votre tableau de bord.',
                'general' => false, 
                'delete' => false, 
            ],
            [ 
                'title' => 'Utilisateur inscrit',
                'description' => 'Un nouvel utilisateur s\'est inscrit sur la plateforme.',
                'general' => true, 
                'delete' => false, 

            ],
            [ 
                'title' => 'Défi validé',
                'description' => 'Un défi a été validé et est maintenant visible pour tous les utilisateurs.',
                'general' => false, 
                'delete' => false, 

            ],
            [
                'title' => 'Défi expiré',
                'description' => 'Le défi que vous suiviez a expiré.',
                'general' => true, 
                'delete' => true, 

            ],
            [ 
                'title' => 'Nouvelle mise à jour disponible',
                'description' => 'Une nouvelle mise à jour de la plateforme est maintenant disponible. Veuillez vérifier les nouveautés.',
                'general' => true, 
                'delete' => false, 

            ],
            [ 
                'title' => 'Message reçu',
                'description' => 'Vous avez reçu un nouveau message de l\'administrateur.',
                'general' => false, 
                'delete' => false, 

            ],
            [ 
                'title' => 'Réunion planifiée',
                'description' => 'Une réunion a été planifiée pour discuter des mises à jour du projet.',
                'general' => true, 
                'delete' => true, 

            ],
            [ 
                'title' => 'Rappel de tâche',
                'description' => 'Il vous reste une tâche à accomplir. Veuillez la finaliser avant la fin de la journée.',
                'general' => false, 
                'delete' => false, 

            ],
            [ 
                'title' => 'Changement de mot de passe',
                'description' => 'Votre mot de passe a été modifié avec succès.',
                'general' => true, 
                'delete' => false, 

            ],
            [ 
                'title' => 'Alerte de sécurité',
                'description' => 'Une tentative de connexion suspecte a été détectée sur votre compte.',
                'general' => true, 
                'delete' => false, 

            ],
        ];

        Notification::insert($notifications);
    }
}
