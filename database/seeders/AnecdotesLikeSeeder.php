<?php

namespace Database\Seeders;

use App\Models\AnecdotesLike;
use Illuminate\Database\Seeder;

class AnecdotesLikeSeeder extends Seeder
{
    public function run(): void
    {
        AnecdotesLike::factory(50)->create();
    }
}
