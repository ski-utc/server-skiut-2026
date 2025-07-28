<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Attention à l'ordre, chaque bloc (sauf le premier) représentent des tables dépendantes
        $this->call([
            // Tables indépendantes
            ContactSeeder::class,
            ChallengeSeeder::class,
            ActivitySeeder::class,
            NotificationSeeder::class,
            TransportSeeder::class,

            RoomSeeder::class,
            UserSeeder::class,
            RelationsSeeder::class,

            AnecdoteSeeder::class,
            UserPerformanceSeeder::class,
            ChallengeProofSeeder::class,
            PushTokenSeeder::class,

            SkinderLikeSeeder::class,
            AnecdotesLikeSeeder::class,
            AnecdotesWarnSeeder::class,
        ]);
    }
}
