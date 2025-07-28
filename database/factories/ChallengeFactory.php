<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Challenge>
 */
class ChallengeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $challenges = [
            'Tire fesse sans les skis' => 20,
            'Skier sur ses chaussures' => 20,
            'Faire un tour en dameuse' => 30,
            'Photo avec un/une mono ESF' => 10,
            'Faire le plus beau dessin de pipi dans la neige' => 15,
            'Sauter du télésiège' => 20,
            'Se raser les cheveux' => 5,0,
            "Boire une biere avant l'ouverture des pistes (bière du p'tit dej)" => 25,
            'Faire une chorégraphie en équipe en ski (ski sychronisé)' => 15,
            'Présentation Top Chef du repas à partir du pack bouffe' => 10,
            'Skier en maillot de bain ou en kilt' => 30,
            "S'infiltrer en carré VIP dans le pano bar" => 30,
            'Remplacer le percheman au départ du tire fesse' => 35,
            'Passer la pelle avant le télésiège' => 35,
            'Faire un igloo' => 40,
            'Faire une manif UVR (Union Vars-Risoul)' => 20,
            'Ne pas descendre du telesiège' => 20,
            "Prendre le tirefesse à l'envers" => 20,
            'Faire du ski/snow à deux sur une seule paire' => 15,
            'Passer sous un autre skieur' => 5,
            'Faire du ski assis sur une chaise posée sur ses skis' => 15,
            'Faire une journée déguisée sur les pistes avec toute la chambre' => 30,
            'Chanter "quand te reverrais-je" sur un télésiège en panne' => 5,
            'Jeter des boules de neige sur les gens sous le télésiège' => 5,
            "Se mettre dans la file d'un cours d'enfants ESF" => 30,
            'Décorer de A à Z sa chambre' => 20,
            "Aller chier dans les toilettes d'une chambre inconnue" => 10,
            'Chier sur une piste' => 20,
            "Faire un bisou sur le crâne d'un chauve" => 5,
            "Déchausser des inconnus dans la file d'un telesiège" => 10,
            'Faire une raclette sur le télésiège' => 30,
            'Faire un ventregliss sur une piste rouge' => 40,
            'Se prendre un sapin en ski/snow' => 20,
            'Se faire passer pour un mono de ski avec un accent marseillais' => 15,
            'Descendre une piste sur son matelas' => 70,
            'Dépasser les 10,0,km/h de vitesse max sur Strava' => 30,
            'Prendre une photo avec le charlie des pistes' => 25,
            'Faire un petit dej au lit à la team anim et à la team info' => 10,
            'Aller se plaindre que ça bug à la team info' => -5,
            'Faire un dessin à la team anim' => 10,
            'Faire une chanson à la team anim' => 10,
            'Faire un virement à la team anim' => 10,
            'Donner des ailes à la sponso' => 10,
            'Faire une manif devant la team log' => 10,
            'Faire les paparrazi à juliette et nico' => 10,
            "Demander à nico ce que ça fait d'être secretaire" => 10,
            "Ramener de l'ambiance sur les pistes" => 10,
            'Faire un Subway Surfer en ski/snow' => 15,
            'Faire un Mario Kart en haut des pistes' => 20,
            'Faire la plus grande chenille sur les pistes' => 20,
            "Faire sa meilleure figure sur l'air bag" => 15,
            'Faire un tour en hélico' => 100,
            'Rentrer dans un bar avec les skis au pieds' => 20,
            'Payer la tournée à la team info' => 20,
        ];

        static $index = 0;    // index statique pour éviter les doublons
        $challengeKeys = array_keys($challenges);

        if ($index >= count($challengeKeys)) {
            $challenge = 'Défi personnalisé ' . ($index + 1);    // Si on a utilisé tous les défis, on génère des défis uniques
            $points = fake()->numberBetween(50, 500);
        } else {
            $challenge = $challengeKeys[$index];
            $points = $challenges[$challenge];
        }

        $index++;

        return [
            'title' => $challenge,
            'nbPoints' => $points,
        ];
    }
}
