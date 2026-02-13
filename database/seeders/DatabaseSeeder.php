<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
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
            ShotgunsSeeder::class,
            RoomShotgunSeeder::class,
            BackOfficeAdminSeeder::class,

            RoomSeeder::class,
            UserSeeder::class,
            RelationsSeeder::class,
            PushTokenSeeder::class,

            AnecdoteSeeder::class,
            ChallengeProofSeeder::class,

            SkinderLikeSeeder::class,
            AnecdotesLikeSeeder::class,
            AnecdotesWarnSeeder::class,

            MonoprutSeeder::class,
            PerformanceSeeder::class,
            PermanenceSeeder::class,
            RoomTourSeeder::class,
        ]);
    }
}
