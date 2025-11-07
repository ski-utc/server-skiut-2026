<?php

namespace Database\Seeders;

use App\Models\AnecdotesWarn;
use Illuminate\Database\Seeder;

class AnecdotesWarnSeeder extends Seeder
{
    public function run(): void
    {
        AnecdotesWarn::factory(15)->create();
    }
}
