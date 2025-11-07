<?php

namespace Database\Seeders;

use App\Models\BackOfficeAdmin;
use Illuminate\Database\Seeder;

class BackOfficeAdminSeeder extends Seeder
{
    public function run(): void
    {
        BackOfficeAdmin::factory(5)->create();
        
        BackOfficeAdmin::factory()->create([
            'email' => 'admin@skiut.local',
        ]);
        
        BackOfficeAdmin::factory()->create([
            'email' => 'superadmin@skiut.local',
        ]);        
    }
}
