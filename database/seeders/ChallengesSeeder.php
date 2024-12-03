<?php
namespace Database\Seeders;

use App\Models\Challenge;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChallengesSeeder extends Seeder
{
    public function run()
    {
        DB::table('challenges')->truncate(); // Attention ! supprime tout 
        $challenges = [
            ['title' => 'Tire fesse sans les skis', 'nbPoints' => 20],
            ['title' => 'Skier sur ses chaussures', 'nbPoints' => 20],
            ['title' => 'Faire un tour en dameuse', 'nbPoints' => 30],
            ['title' => 'Photo avec un/une mono ESF', 'nbPoints' => 10],
            ['title' => 'Faire le plus beau dessin de pipi dans la neige', 'nbPoints' => 15],
            ['title' => 'Sauter du télésiège', 'nbPoints' => 20],
            ['title' => 'Se raser les cheveux', 'nbPoints' => 50],
            ['title' => 'Boire une biere avant l\'ouverture des pistes (bière du p\'tit dej)', 'nbPoints' => 25],
            ['title' => 'Faire une chorégraphie en équipe en ski (ski sychronisé)', 'nbPoints' => 15],
            ['title' => 'Présentation Top Chef du repas à partir du pack bouffe', 'nbPoints' => 10],
            ['title' => 'Skier en maillot de bain ou en kilt', 'nbPoints' => 30],
            ['title' => 'S\'infiltrer en carré VIP dans le pano bar', 'nbPoints' => 30],
            ['title' => 'Remplacer le percheman au départ du tire fesse', 'nbPoints' => 35],
            ['title' => 'Passer la pelle avant le télésiège', 'nbPoints' => 35],
            ['title' => 'Faire un igloo', 'nbPoints' => 40],
            ['title' => 'Faire une manif UVR (Union Vars-Risoul)', 'nbPoints' => 20],
            ['title' => 'Ne pas descendre du telesiège', 'nbPoints' => 20],
            ['title' => 'Prendre le tirefesse à l\'envers', 'nbPoints' => 20],
            ['title' => 'Faire du ski/snow à deux sur une seule paire', 'nbPoints' => 15],
            ['title' => 'Passer sous un autre skieur', 'nbPoints' => 5],
            ['title' => 'Faire du ski assis sur une chaise posée sur ses skis', 'nbPoints' => 15],
            ['title' => 'Faire une journée déguisée sur les pistes avec toute la chambre', 'nbPoints' => 30],
            ['title' => 'Chanter "quand te reverrais-je" sur un télésiège en panne', 'nbPoints' => 5],
            ['title' => 'Jeter des boules de neige sur les gens sous le télésiège', 'nbPoints' => 5],
            ['title' => 'Se mettre dans la file d\'un cours d\'enfants ESF', 'nbPoints' => 30],
            ['title' => 'Décorer de A à Z sa chambre', 'nbPoints' => 20],
            ['title' => 'Aller chier dans les toilettes d\'une chambre inconnue', 'nbPoints' => 10],
            ['title' => 'Chier sur une piste', 'nbPoints' => 20],
            ['title' => 'Faire un bisou sur le crâne d\'un chauve', 'nbPoints' => 5],
            ['title' => 'Déchausser des inconnus dans la file d\'un telesiège', 'nbPoints' => 10],
            ['title' => 'Faire une raclette sur le télésiège', 'nbPoints' => 30],
            ['title' => 'Faire un ventregliss sur une piste rouge', 'nbPoints' => 40],
            ['title' => 'Se prendre un sapin en ski/snow', 'nbPoints' => 20],
            ['title' => 'Se faire passer pour un mono de ski avec un accent marseillais', 'nbPoints' => 15],
            ['title' => 'Descendre une piste sur son matelas', 'nbPoints' => 70],
            ['title' => 'Dépasser les 100km/h de vitesse max sur Strava', 'nbPoints' => 30],
            ['title' => 'Prendre une photo avec le charlie des pistes', 'nbPoints' => 25],
            ['title' => 'Faire un petit dej au lit à la team anim et à la team info', 'nbPoints' => 10],
            ['title' => 'Aller se plaindre que ça bug à la team info', 'nbPoints' => 10],
            ['title' => 'Faire un dessin à la team anim', 'nbPoints' => 10],
            ['title' => 'Faire une chanson à la team anim', 'nbPoints' => 10],
            ['title' => 'Faire un virement à la team anim', 'nbPoints' => 10],
            ['title' => 'Donner des ailes à la sponso', 'nbPoints' => 10],
            ['title' => 'Faire une manif devant la team log', 'nbPoints' => 10],
            ['title' => 'Faire les paparrazi à juliette et nico', 'nbPoints' => 10],
            ['title' => 'Demander à nico ce que ça fait d\'être secretaire', 'nbPoints' => 10],
            ['title' => 'Ramener de l\'ambiance sur les pistes', 'nbPoints' => 10],
            ['title' => 'Faire un Subway Surfer en ski/snow', 'nbPoints' => 15],
            ['title' => 'Faire un Mario Kart en haut des pistes', 'nbPoints' => 20],
            ['title' => 'Faire la plus grande chenille sur les pistes', 'nbPoints' => 20],
            ['title' => 'Faire sa meilleure figure sur l\'air bag', 'nbPoints' => 15],
            ['title' => 'Faire un tour en hélico', 'nbPoints' => 100],
            ['title' => 'Rentrer dans un bar avec les skis au pieds', 'nbPoints' => 20],
        ];

        foreach ($challenges as $challenge) {
            Challenge::create($challenge);
        }
    }
}
