<?php

namespace Database\Seeders;

use App\Models\ChallengeProof;
use Illuminate\Database\Seeder;

class ChallengeProofSeeder extends Seeder
{
    public function run(): void
    {
        ChallengeProof::factory(40)->create();
    }
}
