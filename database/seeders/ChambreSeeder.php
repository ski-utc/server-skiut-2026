<?php

namespace Database\Seeders;

use App\Models\Chambre;
use Illuminate\Database\Seeder;

class ChambreSeeder extends Seeder
{
    public function run(): void
    {
        $chambres = [
            [
                'numero' => '101',
                'nb_places' => 2,
                'ambiance' => 'calme',
            ],
            [
                'numero' => '102',
                'nb_places' => 3,
                'ambiance' => 'petite night',
            ],
            [
                'numero' => '103',
                'nb_places' => 4,
                'ambiance' => 'grosse night',
            ],
            [
                'numero' => '201',
                'nb_places' => 2,
                'ambiance' => 'calme',
            ],
            [
                'numero' => '202',
                'nb_places' => 3,
                'ambiance' => 'petite night',
            ],
            [
                'numero' => '203',
                'nb_places' => 4,
                'ambiance' => 'mega grosse night',
            ],
            [
                'numero' => '301',
                'nb_places' => 2,
                'ambiance' => 'calme',
            ],
            [
                'numero' => '302',
                'nb_places' => 3,
                'ambiance' => 'petite night',
            ],
            [
                'numero' => '303',
                'nb_places' => 4,
                'ambiance' => 'grosse night',
            ],
            [
                'numero' => '401',
                'nb_places' => 2,
                'ambiance' => 'calme',
            ],
        ];

        foreach ($chambres as $chambre) {
            Chambre::create($chambre);
        }
    }
}
