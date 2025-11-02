<?php

namespace Database\Seeders;

use App\Models\Monoprut;
use App\Models\Room;
use Illuminate\Database\Seeder;

class MonoprutSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rooms = Room::all();

        if ($rooms->isEmpty()) {
            $this->command->warn('Aucune chambre trouvée pour créer les offres monoprut');
            return;
        }

        $products = [
            'fruit' => ['Pommes', 'Bananes', 'Oranges', 'Poires', 'Fraises', 'Raisins'],
            'veggie' => ['Carottes', 'Tomates', 'Salade', 'Concombres', 'Poivrons', 'Courgettes'],
            'drink' => ['Eau', 'Soda', 'Jus d\'orange', 'Jus de pomme', 'Coca', 'Limonade'],
            'sweet' => ['Chocolat', 'Bonbons', 'Gâteaux', 'Cookies', 'Glace', 'Donuts'],
            'snack' => ['Chips', 'Biscuits', 'Crackers', 'Pop-corn', 'Noix', 'Barres céréales'],
            'dairy' => ['Lait', 'Yaourt', 'Fromage', 'Beurre', 'Crème', 'Fromage blanc'],
            'bread' => ['Pain', 'Baguette', 'Croissants', 'Pain de mie', 'Brioche', 'Pains au chocolat'],
            'meat' => ['Poulet', 'Jambon', 'Saucisses', 'Steak', 'Bacon', 'Dinde'],
            'fish' => ['Saumon', 'Thon', 'Sardines', 'Cabillaud', 'Crevettes', 'Truite'],
            'grain' => ['Riz', 'Pâtes', 'Quinoa', 'Semoule', 'Céréales', 'Flocons d\'avoine'],
            'other' => ['Sel', 'Poivre', 'Huile', 'Épices', 'Sauce', 'Condiments']
        ];

        // Créer des offres disponibles (sans récepteur)
        for ($i = 0; $i < 30; $i++) {
            $type = fake()->randomElement(array_keys($products));
            Monoprut::create([
                'product' => fake()->randomElement($products[$type]),
                'quantity' => (string) fake()->numberBetween(1, 10),
                'type' => $type,
                'giver_room_id' => $rooms->random()->id,
                'receiver_room_id' => null,
                'retrieved' => false,
            ]);
        }

        // Créer des offres réservées (avec récepteur, non récupérées)
        for ($i = 0; $i < 15; $i++) {
            $type = fake()->randomElement(array_keys($products));
            $giverRoom = $rooms->random();
            $receiverRoom = $rooms->where('id', '!=', $giverRoom->id)->random();
            
            Monoprut::create([
                'product' => fake()->randomElement($products[$type]),
                'quantity' => (string) fake()->numberBetween(1, 10),
                'type' => $type,
                'giver_room_id' => $giverRoom->id,
                'receiver_room_id' => $receiverRoom->id,
                'retrieved' => false,
            ]);
        }

        // Créer des offres récupérées (avec récepteur, récupérées)
        for ($i = 0; $i < 10; $i++) {
            $type = fake()->randomElement(array_keys($products));
            $giverRoom = $rooms->random();
            $receiverRoom = $rooms->where('id', '!=', $giverRoom->id)->random();
            
            Monoprut::create([
                'product' => fake()->randomElement($products[$type]),
                'quantity' => (string) fake()->numberBetween(1, 10),
                'type' => $type,
                'giver_room_id' => $giverRoom->id,
                'receiver_room_id' => $receiverRoom->id,
                'retrieved' => true,
            ]);
        }

        $this->command->info('Offres monoprut créées avec succès !');
    }
}
