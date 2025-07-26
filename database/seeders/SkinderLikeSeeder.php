<?php

namespace Database\Seeders;

use App\Models\SkinderLike;
use Illuminate\Database\Seeder;

class SkinderLikeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SkinderLike::factory(30)->create();
    }
} 