<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\NewNotification;
use Illuminate\Console\Command;

class SendNotificationToMembers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:send-to-members {--title=} {--text=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envoie les notifications aux membres';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $title = $this->option('title');
        $text = $this->option('text');

        if (!$title || !$text) {
            $this->error('Les paramètres --title et --text sont obligatoires.');
            return 1;
        }

        $this->info("Envoi de la notification : \"$title\" - \"$text\" aux membres...");

        $members = User::membersOnly()->get();
        $count = 0;

        $bar = $this->output->createProgressBar(count($members));
        $bar->start();

        foreach ($members as $member) {
            try {
                $member->notify(new NewNotification([
                    'title' => $title,
                    'content' => $text,
                    'data' => [
                        'type' => 'general_announcement'
                    ]
                ]));
                $count++;
            } catch (\Exception $e) {
                $this->error("Erreur pour {$member->email}: " . $e->getMessage());
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Notification envoyée à $count membres.");

        return 0;
    }
}
