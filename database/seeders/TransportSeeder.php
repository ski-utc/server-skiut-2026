<?php

namespace Database\Seeders;

use App\Models\Transport;
use Illuminate\Database\Seeder;

class TransportSeeder extends Seeder
{
    public function run(): void
    {
        Transport::factory(20)->create();
    }
}
