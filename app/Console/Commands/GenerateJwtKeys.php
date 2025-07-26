<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateJwtKeys extends Command
{
    protected $signature = 'jwt:generate';
    protected $description = 'Génère une paire de clés RSA pour les JWT';

    public function handle(): int
    {
        $privateKeyPath = storage_path(env('JWT_PRIVATE_KEY_PATH', 'app/private/private.pem'));
        $publicKeyPath = storage_path(env('JWT_PUBLIC_KEY_PATH', 'app/private/public.pem'));

        $dir = dirname($privateKeyPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            $this->info("Répertoire créé : $dir");
        }

        if (!file_exists($privateKeyPath)) {
            $this->info('Génération de la clé privée...');
            exec("openssl genpkey -algorithm RSA -out \"$privateKeyPath\" -pkeyopt rsa_keygen_bits:2048");
        } else {
            $this->info("Clé privée déjà existante : $privateKeyPath");
        }

        if (!file_exists($publicKeyPath)) {
            $this->info('Génération de la clé publique...');
            exec("openssl rsa -pubout -in \"$privateKeyPath\" -out \"$publicKeyPath\"");
        } else {
            $this->info("Clé publique déjà existante : $publicKeyPath");
        }

        $this->info('----- Clés JWT générées avec succès. -----');
        return 0;
    }
}
