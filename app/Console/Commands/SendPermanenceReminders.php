<?php

namespace App\Console\Commands;

use App\Http\Controllers\PermanenceController;
use App\Services\FirebaseNotificationService;
use Illuminate\Console\Command;

class SendPermanenceReminders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'permanence:send-reminders';

    /**
     * The console command description.
     */
    protected $description = 'Envoie les rappels de permanences 1 heure avant le début';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Envoi des rappels de permanences...');

        try {
            $firebaseService = app(FirebaseNotificationService::class);
            $controller = new PermanenceController($firebaseService);

            $result = $controller->sendReminders();

            $data = $result->getData(true);

            if ($data['success']) {
                $this->info($data['message']);
                return 0;
            } else {
                $this->error('Erreur: ' . $data['message']);
                return 1;
            }

        } catch (\Exception $e) {
            $this->error('Erreur lors de l\'envoi des rappels: ' . $e->getMessage());
            return 1;
        }
    }
}
