<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActivitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('activities')->truncate();
        $activities = [
            ['date' => '2025-01-18', 'text' => 'Tournée des chambres', 'startTime' => '20:30', 'endTime' => '21:30', 'payant' => false],
            ['date' => '2025-01-19', 'text' => 'Distribution petit-déjeuner', 'startTime' => '08:00', 'endTime' => '09:00', 'payant' => true],
            ['date' => '2025-01-19', 'text' => 'Location ski', 'startTime' => '09:00', 'endTime' => '11:30', 'payant' => false],
            ['date' => '2025-01-19', 'text' => 'Petit-déjeuner Yoonly', 'startTime' => '11:30', 'endTime' => '12:30', 'payant' => false],
            ['date' => '2025-01-19', 'text' => 'Barbecue Yoonly', 'startTime' => '12:30', 'endTime' => '13:30', 'payant' => false],
            ['date' => '2025-01-19', 'text' => 'Cours de ski / snow ESF (débutant / intermédiaire / confirmé)', 'startTime' => '12:30', 'endTime' => '15:00', 'payant' => true],
            ['date' => '2025-01-19', 'text' => 'Course d\'orientation', 'startTime' => '14:00', 'endTime' => '17:00', 'payant' => false],
            ['date' => '2025-01-19', 'text' => 'Afterski Doume\'s', 'startTime' => '17:00', 'endTime' => '19:00', 'payant' => false],
            ['date' => '2025-01-19', 'text' => 'Tournée des chambres', 'startTime' => '20:30', 'endTime' => '21:30', 'payant' => false],
            ['date' => '2025-01-19', 'text' => 'Grotte du Yéti', 'startTime' => '21:30', 'endTime' => '22:30', 'payant' => false],
            ['date' => '2025-01-19', 'text' => 'Vers le Smithy\'s (canettes Red Bull offertes)', 'startTime' => '22:30', 'endTime' => '23:00', 'payant' => false],
            ['date' => '2025-01-19', 'text' => 'Barathon au Smithy\'s', 'startTime' => '23:00', 'endTime' => '01:30', 'payant' => false],
            ['date' => '2025-01-20', 'text' => 'Distribution petit-déjeuner', 'startTime' => '08:00', 'endTime' => '09:00', 'payant' => true],
            ['date' => '2025-01-20', 'text' => 'Cours de ski / snow ESF (débutant / intermédiaire / confirmé)', 'startTime' => '12:30', 'endTime' => '15:00', 'payant' => true],
            ['date' => '2025-01-20', 'text' => 'Parapente', 'startTime' => '15:00', 'endTime' => '17:30', 'payant' => true],
            ['date' => '2025-01-20', 'text' => 'Escape game', 'startTime' => '15:30', 'endTime' => '17:30', 'payant' => true],
            ['date' => '2025-01-20', 'text' => 'Snakegliss (groupe 1)', 'startTime' => '16:30', 'endTime' => '18:30', 'payant' => true],
            ['date' => '2025-01-20', 'text' => 'Karaoké', 'startTime' => '18:00', 'endTime' => '19:00', 'payant' => false],
            ['date' => '2025-01-20', 'text' => 'Tournée des chambres', 'startTime' => '20:30', 'endTime' => '21:30', 'payant' => false],
            ['date' => '2025-01-20', 'text' => 'Soirée Yoonly moustache (uniquement) (barbe interdite) à la Grotte du Yéti', 'startTime' => '21:30', 'endTime' => '01:30', 'payant' => false],
            ['date' => '2025-01-21', 'text' => 'Distribution petit-déjeuner', 'startTime' => '08:00', 'endTime' => '09:00', 'payant' => true],
            ['date' => '2025-01-21', 'text' => 'Sortie VTT', 'startTime' => '9:00', 'endTime' => '11:30', 'payant' => true],
            ['date' => '2025-01-21', 'text' => 'Cours de ski / snow ESF (débutant / intermédiaire / confirmé)', 'startTime' => '12:30', 'endTime' => '15:00', 'payant' => true],
            ['date' => '2025-01-21', 'text' => 'Grande descente from 3600 to 1300 - Shotgun avec décharge de responsabilité', 'startTime' => '14:00', 'endTime' => '16:30', 'payant' => false],
            ['date' => '2025-01-21', 'text' => 'Laser game', 'startTime' => '15:30', 'endTime' => '17:30', 'payant' => true],
            ['date' => '2025-01-21', 'text' => 'Ski de nuit', 'startTime' => '17:30', 'endTime' => '20:00', 'payant' => false],
            ['date' => '2025-01-21', 'text' => 'Tournée des chambres', 'startTime' => '20:30', 'endTime' => '21:30', 'payant' => false],
            ['date' => '2025-01-21', 'text' => 'Soirée lettres', 'startTime' => '21:30', 'endTime' => '01:30', 'payant' => false],
            ['date' => '2025-01-22', 'text' => 'Distribution petit-déjeuner', 'startTime' => '08:00', 'endTime' => '09:00', 'payant' => true],
            ['date' => '2025-01-22', 'text' => 'POMA (constructeur du nouveau téléphérique Jandri 3S)', 'startTime' => '10:00', 'endTime' => '18:30', 'payant' => true],
            ['date' => '2025-01-22', 'text' => 'Cours de ski by Bibiche et Hubert', 'startTime' => '11:00', 'endTime' => '12:00', 'payant' => false],
            ['date' => '2025-01-22', 'text' => 'Snakegliss (groupe 2)', 'startTime' => '16:30', 'endTime' => '18:30', 'payant' => true],
            ['date' => '2025-01-22', 'text' => 'Afterski concours de bonhomme de neige / bataille de boule de neige géante', 'startTime' => '17:00', 'endTime' => '19:30', 'payant' => false],
            ['date' => '2025-01-22', 'text' => 'Tournée des chambres', 'startTime' => '20:30', 'endTime' => '21:30', 'payant' => false],
            ['date' => '2025-01-22', 'text' => 'Fête des voisins', 'startTime' => '21:30', 'endTime' => '00:00', 'payant' => false],
            ['date' => '2025-01-22', 'text' => 'Avalanche', 'startTime' => '00:00', 'endTime' => '01:30', 'payant' => false],
            ['date' => '2025-01-23', 'text' => 'Distribution petit-déjeuner', 'startTime' => '08:00', 'endTime' => '09:00', 'payant' => true],
            ['date' => '2025-01-23', 'text' => 'Concours de figures au snowpark', 'startTime' => '10:00', 'endTime' => '12:30', 'payant' => false],
            ['date' => '2025-01-23', 'text' => 'Cours de ski / snow ESF freeride', 'startTime' => '14:30', 'endTime' => '17:30', 'payant' => true],
            ['date' => '2025-01-23', 'text' => 'Ski de nuit', 'startTime' => '17:30', 'endTime' => '20:00', 'payant' => false],
            ['date' => '2025-01-23', 'text' => 'Tournoi de beerpong', 'startTime' => '18:00', 'endTime' => '19:30', 'payant' => false],
            ['date' => '2025-01-23', 'text' => 'Tournée des chambres', 'startTime' => '20:30', 'endTime' => '21:30', 'payant' => false],
            ['date' => '2025-01-23', 'text' => 'Soirée Yoonly masques (mais pas de ski)', 'startTime' => '21:30', 'endTime' => '01:30', 'payant' => false],
            ['date' => '2025-01-24', 'text' => 'Distribution petit-déjeuner', 'startTime' => '08:00', 'endTime' => '09:00', 'payant' => true],
            ['date' => '2025-01-24', 'text' => 'Rando raquettes', 'startTime' => '09:00', 'endTime' => '12:30', 'payant' => true],
            ['date' => '2025-01-24', 'text' => 'Luge', 'startTime' => '13:00', 'endTime' => '17:00', 'payant' => true],
            ['date' => '2025-01-24', 'text' => 'Afterski Doume\'s', 'startTime' => '17:00', 'endTime' => '19:00', 'payant' => false],
            ['date' => '2025-01-24', 'text' => 'Tournée des chambres', 'startTime' => '20:30', 'endTime' => '21:30', 'payant' => false],
            ['date' => '2025-01-24', 'text' => 'Soirée chronologique', 'startTime' => '21:30', 'endTime' => '01:30', 'payant' => false],
            ['date' => '2025-01-25', 'text' => 'Départ des Deux Alpes', 'startTime' => '09:00', 'endTime' => '20:00', 'payant' => false],
        ];

        DB::table('activities')->insert($activities);
    }
}
