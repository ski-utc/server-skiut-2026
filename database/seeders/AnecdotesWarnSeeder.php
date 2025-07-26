<?php

namespace Database\Seeders;

use App\Models\AnecdotesWarn;
use Illuminate\Database\Seeder;

class AnecdotesWarnSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AnecdotesWarn::factory(15)->create();
    }
} 