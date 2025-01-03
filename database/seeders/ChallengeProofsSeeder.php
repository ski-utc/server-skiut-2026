<?php
namespace Database\Seeders;

use App\Models\ChallengeProof;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChallengeProofsSeeder extends Seeder
{
    public function run()
    {
        DB::table('challenge_proofs')->truncate(); // Attention ! supprime tout 
        $proofs = ChallengeProof::create([
        'file' => 'example.jpg',
        'nb_likes' => 5,
        'valid' => true,
        'alert' => 0,
        'delete' => false,
        'active' => true,
        'room_id' => 1,
        'user_id' => 316366,
        'challenge_id' => 53,
        ]);

    }
}
