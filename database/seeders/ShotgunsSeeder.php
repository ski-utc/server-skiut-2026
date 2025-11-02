<?php

namespace Database\Seeders;

use App\Models\Shotguns;
use Illuminate\Database\Seeder;

class ShotgunsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer 50 participants au shotgun
        Shotguns::factory(50)->create();
        
        $this->command->info('Shotguns créés avec succès !');
    }
}
