<?php

namespace Database\Seeders;

use App\Models\PushToken;
use Illuminate\Database\Seeder;

class PushTokenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PushToken::factory(25)->create();
    }
} 