<?php

namespace Database\Seeders;

use App\Models\BackOfficeAdmin;
use Illuminate\Database\Seeder;

class BackOfficeAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer quelques admins de back-office
        BackOfficeAdmin::factory(5)->create();
        
        // Ajouter des emails de test connus
        BackOfficeAdmin::factory()->create([
            'email' => 'admin@skiut.local',
        ]);
        
        BackOfficeAdmin::factory()->create([
            'email' => 'superadmin@skiut.local',
        ]);
        
        $this->command->info('Admins back-office créés avec succès !');
    }
}
