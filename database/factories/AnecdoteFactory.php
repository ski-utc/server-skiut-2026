<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AnecdoteFactory extends Factory
{
    public function definition(): array
    {
        $anecdotes = [
            "L'onglet notification de l'App a été construit à partir de la page notif de Pokémon TCG",
            'L’appli est dispoooooo',
            'Première piste :  Louis Rodembourg = pas content de la neige Il préfère risoul !',
            'Les pistes en haut de la 2e télécabine sont chèvresques 🐐',
            'Troma crânien après une chute, j’ai perdu la mémoire j’ai oublié que mon ex m’avait quitté. Finalement je me réveille célibataires et fin de skiut après juste 2h de glisse.',
            'Dans ma chambre y’a le plus gros baiseur de Shanghai',
            "En tombant en ski, j'ai voulu faire une Schumacher  en pensant à la MT02. J'ai fait l'étoile en glissant.",
            'Celui qui a dit que les pistes sont chevresques OOH TÖCAAAARD !! JE ME LE METS 2EME CELUI LA',
            "Rester bloqué devant notre porte de chambre parce qu'on avait pas les clés. Appeler la sécu pour nous ouvrir, se rendre compte une fois la chambre ouverte que les clés étaient dans une de nos poches 💀",
            'Première fois au ski, on se deter à aller sur la piste verte (avant tout cours bien évidemment parce qu’on est pas des salopes ici). Merveilleuse idée puisque j’ai dévalé la moitié de la piste full speed (la classe) pour foncer droit dans le décor. Petit cocard en prime, ca rajoute du style 🤘🏻',
            "Le dev de la team Info, dépassé par les bugs sur l'App, a sauté du télésiège (true story) (faites pas ça la team c'est pas malin)",
            'Il faut se raser la barbe pour la soirée moustache.. J’appelle tous les barbus à la résistance ! Qui est chaud pour une contre-soirée des barbus ? Hahaha.',
            'Thème été (molky, pétanque, danse, musique, ambiance, plage)',
            'Si vous rencontrez une fille italienne, demandez-lui comment perdre un snowboard en moins de 15 secondes au milieu de la montagne ',
            'Come to 207,Ana de Armas is here!!!!',
            'Venez à la chambre 207, Ana de Armas est la bas!!!',
            'En chambre 207A DJ Fuji',
            'domino de piou piou',
            'Étant encore bourrée d’hier mes potes m’ont laissé dormir sur le bord de la piste pendant 1h30, puis j’ai vomi et jsuis repartie',
            "J'ai plus de bleus que de pistes descendues",
            'Jolie saut d’environ 6 mètres de la part de notre joli trésorier ! À couper le souffle',
            'Dîner brésilien à la chambre 207!!',
            'En voulant rentrer à 3heures du matin, quelle fût ma surprise lorsque j’ai réalisé que la porte était fermée à clefs et que personne ne répondait  Merci à la chambre qui m’a hébergé mdrrr',
            "Un mec random est venu fouiller notre frigo à 3h du matin en quête d'un coca-cola. Surprenant"
        ];

        return [
            'text' => fake()->randomElement($anecdotes),
            'room_id' => fake()->numberBetween(1, 15),
            'user_id' => fake()->numberBetween(1, 30),
            'valid' => fake()->boolean(80),
            'delete' => fake()->boolean(5),
            'active' => fake()->boolean(90),
        ];
    }
}
