<?php

namespace Database\Seeders;

use App\Models\AnecdotesLike;
use Illuminate\Database\Seeder;

class AnecdotesLikeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AnecdotesLike::factory(50)->create();
    }
} 