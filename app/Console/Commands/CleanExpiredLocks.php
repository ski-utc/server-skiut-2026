<?php

namespace App\Console\Commands;

use App\Models\RoomShotgun;
use Illuminate\Console\Command;

class CleanExpiredLocks extends Command
{
    protected $signature = 'chambres:clean-locks';
    protected $description = 'Nettoyer les verrous de chambres expirés';

    public function handle()
    {
        $expiredLocks = RoomShotgun::where('locked_until', '<', now())->get();

        foreach ($expiredLocks as $roomShotgun) {
            $roomShotgun->unlock();
            $this->info("Chambre {$roomShotgun->numero} débloquée");
        }

        $this->info("Nettoyage terminé. {$expiredLocks->count()} chambres débloquées.");
    }
}
