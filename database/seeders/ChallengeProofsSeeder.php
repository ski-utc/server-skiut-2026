<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ChallengeProof;
use Illuminate\Support\Facades\DB;


class ChallengeProofsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('challenge_proofs')->truncate();

        $challengeProofs = [
            [ # validé
                'id'            => 1,
                'file'          => 'proof1.png',
                'nb_likes'       => 10,
                'valid'         => true,
                'alert'         => false,
                'delete'        => false,
                'room_id'       => 52,
                'user_id'       => 316050,
                'challenge_id'  => 1,
            ],
            [ # en attente de validation
                'id'            => 2,
                'file'          => 'proof2.jpg',
                'nb_likes'       => 0,
                'valid'         => false,
                'alert'         => true,
                'delete'        => false,
                'room_id'       => 52,
                'user_id'       => 316050,
                'challenge_id'  => 2,
            ],
            [ # validé
                'id'            => 3,
                'file'          => 'proof3.jpg',
                'nb_likes'       => 20,
                'valid'         => true,
                'alert'         => false,
                'delete'        => false,
                'room_id'       => 53,
                'user_id'       => 316366,
                'challenge_id'  => 1,
            ],
            [ # supprimé
                'id'            => 4,
                'file'          => 'proof4.png',
                'nb_likes'       => 8,
                'valid'         => false,
                'alert'         => true,
                'delete'        => true,
                'room_id'       => 53,
                'user_id'       => 316377,
                'challenge_id'  => 1,
            ],
            [ # validé
                'id'            => 5,
                'file'          => 'proof5.png',
                'nb_likes'       => 12,
                'valid'         => true,
                'alert'         => false,
                'delete'        => false,
                'room_id'       => 53,
                'user_id'       => 316617,
                'challenge_id'  => 5,
            ],
            [ # validé
                'id'            => 6,
                'file'          => 'proof6.jpg',
                'nb_likes'       => 25,
                'valid'         => true,
                'alert'         => false,
                'delete'        => false,
                'room_id'       => 53,
                'user_id'       => 316617,
                'challenge_id'  => 6,
            ],
            [ # en attente de validation
                'id'            => 7,
                'file'          => 'proof7.png',
                'nb_likes'       => 0,
                'valid'         => false,
                'alert'         => true,
                'delete'        => false,
                'room_id'       => 53,
                'user_id'       => 316617,
                'challenge_id'  => 7,
            ],
            [ # validé
                'id'            => 8,
                'file'          => 'proof8.png',
                'nb_likes'       => 18,
                'valid'         => true,
                'alert'         => false,
                'delete'        => false,
                'room_id'       => 52,
                'user_id'       => 316050,
                'challenge_id'  => 8,
            ],
            [ # validé
                'id'            => 9,
                'file'          => 'proof9.jpg',
                'nb_likes'       => 30,
                'valid'         => true,
                'alert'         => false,
                'delete'        => false,
                'room_id'       => 53,
                'user_id'       => 317186,
                'challenge_id'  => 9,
            ],
            [ # supprimé
                'id'            => 10,
                'file'          => 'proof10.png',
                'nb_likes'       => 0,
                'valid'         => false,
                'alert'         => true,
                'delete'        => true,
                'room_id'       => 52,
                'user_id'       => 316050,
                'challenge_id'  => 8,
            ],
        ];

        DB::table('challenge_proofs')->insert($challengeProofs);
    }
}
