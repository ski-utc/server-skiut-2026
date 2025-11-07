<?php

namespace Database\Seeders;

use App\Models\Anecdote;
use Illuminate\Database\Seeder;

class AnecdoteSeeder extends Seeder
{
    public function run(): void
    {
        Anecdote::factory(50)->create();
    }
}
