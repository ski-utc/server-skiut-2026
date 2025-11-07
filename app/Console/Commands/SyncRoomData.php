<?php

namespace App\Console\Commands;

use App\Models\Room;
use App\Models\RoomTourVisit;
use App\Models\User;
use Illuminate\Console\Command;

class SyncRoomData extends Command
{
    protected $signature = 'rooms:sync {--interactive : Mode interactif pour définir les moods}';
    protected $description = 'Synchronise les données des chambres et crée les entrées manquantes';

    private $moodOptions = [
        'chambre_calme' => '🟢 Chambre calme',
        'petite_night' => '🟡 Petite night',
        'grosse_night' => '🟠 Grosse night',
        'mega_grosse_night' => '🔴 Méga grosse night'
    ];

    public function handle()
    {
        $this->info('🔍 Vérification des chambres...');

        // Récupérer tous les room_id uniques depuis room_tour_visits
        $roomIds = RoomTourVisit::select('room_id')->distinct()->pluck('room_id');

        $this->info("📋 {$roomIds->count()} chambres trouvées dans les tournées");

        $created = 0;
        $existing = 0;
        $updated = 0;
        $roomsToUpdate = [];

        foreach ($roomIds as $roomId) {
            // Chercher la chambre dans la table rooms
            $room = Room::where('roomNumber', $roomId)
                       ->orWhere('id', $roomId)
                       ->first();

            if (!$room) {
                // Créer la chambre si elle n'existe pas
                $users = User::where('room_id', $roomId)->get();
                
                $room = Room::create([
                    'roomNumber' => $roomId,
                    'capacity' => $users->count() > 0 ? $users->count() : 4,
                    'name' => null,
                    'mood' => null,
                ]);

                $this->warn("✨ Chambre {$roomId} créée");
                $created++;
                $roomsToUpdate[] = $room;
            } else {
                // Vérifier si name et mood sont définis
                if (!$room->name || !$room->mood) {
                    $this->warn("⚠️  Chambre {$roomId} : name={$room->name}, mood={$room->mood}");
                    $updated++;
                    $roomsToUpdate[] = $room;
                } else {
                    $this->line("✅ Chambre {$roomId} : {$room->name} ({$room->mood})");
                    $existing++;
                }
            }
        }

        $this->newLine();
        $this->info("📊 Résumé :");
        $this->line("  - Chambres existantes complètes : {$existing}");
        $this->line("  - Chambres créées : {$created}");
        $this->line("  - Chambres nécessitant des mises à jour : {$updated}");

        // Mode interactif
        if ($this->option('interactive') && count($roomsToUpdate) > 0) {
            $this->newLine();
            $this->info("🔧 Mode interactif activé !");
            
            foreach ($roomsToUpdate as $room) {
                $this->newLine();
                $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
                $this->info("Chambre {$room->roomNumber}");
                
                // Afficher les occupants
                $users = User::where('room_id', $room->roomNumber)->get();
                if ($users->count() > 0) {
                    $this->line("Occupants : " . $users->pluck('firstName')->implode(', '));
                }

                // Demander le nom si non défini
                if (!$room->name) {
                    $name = $this->ask('Nom de la chambre ?', 'Chambre ' . $room->roomNumber);
                    $room->name = $name;
                }

                // Demander le mood si non défini
                if (!$room->mood) {
                    $moodChoice = $this->choice(
                        'Ambiance de la chambre ?',
                        array_values($this->moodOptions),
                        0
                    );
                    
                    // Retrouver la clé correspondante
                    $mood = array_search($moodChoice, $this->moodOptions);
                    $room->mood = $mood;
                }

                $room->save();
                $this->info("✅ Chambre {$room->roomNumber} mise à jour !");
            }

            $this->newLine();
            $this->info("🎉 Toutes les chambres ont été mises à jour !");
        } elseif (count($roomsToUpdate) > 0) {
            $this->newLine();
            $this->warn("💡 Astuce : Utilisez --interactive pour définir les chambres de manière interactive :");
            $this->line("   php artisan rooms:sync --interactive");
            $this->newLine();
            $this->line("Ou manuellement avec tinker :");
            $this->line("   php artisan tinker");
            $this->line("   >>> \$room = App\\Models\\Room::where('roomNumber', 'NUMERO')->first();");
            $this->line("   >>> \$room->name = 'Nom de la chambre';");
            $this->line("   >>> \$room->mood = 'chambre_calme'; // chambre_calme, petite_night, grosse_night, mega_grosse_night");
            $this->line("   >>> \$room->save();");
        }

        return Command::SUCCESS;
    }
}

