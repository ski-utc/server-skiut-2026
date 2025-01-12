<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Transport;
use App\Models\User;

class TransportSeeder extends Seeder
{
    public function run()
    {
        // Example data to seed for Transports
        $transport1 = Transport::create([
            'departure' => 'Compiègne',
            'arrival' => 'Les Deux Alpes',
            'colour' => 'Blue',
            'type' => 'Aller',
            'horaire_depart' => '08:00:00',
            'horaire_arrivee' => '21:00:00',
        ]);

        $transport2 = Transport::create([
            'departure' => 'Paris',
            'arrival' => 'Les Deux Alpes',
            'colour' => 'Blue',
            'type' => 'Aller',
            'horaire_depart' => '09:00:00',
            'horaire_arrivee' => '21:00:00',
        ]);

        $transport3 = Transport::create([
            'departure' => 'Les Deux Alpes',
            'arrival' => 'Compiègne',
            'colour' => 'Green',
            'type' => 'Retour',
            'horaire_depart' => '10:00:00',
            'horaire_arrivee' => '00:00:00',
        ]);

        // Example of attaching users to the transports using the many-to-many relationship
        // Assuming you have users already created, you can attach them as follows:
        $user1 = User::find(316050); // Replace with actual user ID
        $user2 = User::find(316366); // Replace with actual user ID

        // Attach users to transports (you can add as many as needed)
        $transport1->users()->attach([$user1->id, $user2->id]);
        //$transport2->users()->attach([$user1->id]);
        $transport3->users()->attach([$user2->id]);

        // Add more records as needed
    }
}
