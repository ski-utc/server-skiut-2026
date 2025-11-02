<?php

namespace Database\Seeders;

use App\Models\Challenge;
use Illuminate\Database\Seeder;

class ChallengeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $challenges = [
            'Tire fesse sans les skis' => 20,
            'Skier sur ses chaussures' => 20,
            'Faire un tour en dameuse' => 30,
            'Photo avec un/une mono ESF' => 10,
            'Faire le plus beau dessin de pipi dans la neige' => 15,
            'Sauter du télésiège' => 20,
            'Se raser les cheveux' => 5,
            "Boire une biere avant l'ouverture des pistes (bière du p'tit dej)" => 25,
            'Faire une chorégraphie en équipe en ski (ski sychronisé)' => 15,
            'Présentation Top Chef du repas à partir du pack bouffe' => 10,
            'Skier en maillot de bain ou en kilt' => 30,
            "S'infiltrer en carré VIP dans le pano bar" => 30,
            'Remplacer le percheman au départ du tire fesse' => 35,
            'Passer la pelle avant le télésiège' => 35,
            'Faire un igloo' => 40,
        ];

        foreach ($challenges as $title => $points) {
            Challenge::firstOrCreate(
                ['title' => $title],
                ['nbPoints' => $points]
            );
        }

        $this->command->info('Défis créés avec succès !');
    }
}
