<?php

namespace App\Console\Commands;

use App\Models\Chambre;
use Illuminate\Console\Command;

class CleanExpiredLocks extends Command
{
    protected $signature = 'chambres:clean-locks';
    protected $description = 'Nettoyer les verrous de chambres expirés';

    public function handle()
    {
        $expiredLocks = Chambre::where('locked_until', '<', now())->get();
        
        foreach ($expiredLocks as $chambre) {
            $chambre->unlock();
            $this->info("Chambre {$chambre->numero} débloquée");
        }
        
        $this->info("Nettoyage terminé. {$expiredLocks->count()} chambres débloquées.");
    }
}
