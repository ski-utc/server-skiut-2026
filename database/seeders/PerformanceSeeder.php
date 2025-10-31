<?php

namespace Database\Seeders;

use App\Models\PerformanceSession;
use App\Models\User;
use App\Models\UserPerformance;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PerformanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::limit(15)->get();

        if ($users->isEmpty()) {
            $this->command->warn('Aucun utilisateur trouvé pour créer les performances');
            return;
        }

        foreach ($users as $user) {
            // Créer entre 3 et 12 sessions par utilisateur
            $sessionCount = fake()->numberBetween(3, 12);
            $sessions = [];
            $totalDistance = 0;
            $totalDuration = 0;
            $maxSpeedOverall = 0;
            $totalAverageSpeed = 0;

            for ($i = 0; $i < $sessionCount; $i++) {
                $maxSpeed = fake()->randomFloat(2, 15, 85); // Entre 15 et 85 km/h
                $averageSpeed = fake()->randomFloat(2, 10, $maxSpeed * 0.8);
                $duration = fake()->numberBetween(600, 14400); // 10 min à 4h
                $distance = ($averageSpeed / 3.6) * $duration; // Distance en mètres

                $session = PerformanceSession::create([
                    'user_id' => $user->id,
                    'session_id' => Str::uuid(),
                    'max_speed' => $maxSpeed,
                    'average_speed' => $averageSpeed,
                    'distance' => round($distance, 2),
                    'duration' => $duration,
                    'session_date' => fake()->dateTimeBetween('-30 days', 'now'),
                ]);

                $sessions[] = $session;
                $totalDistance += $distance;
                $totalDuration += $duration;
                $maxSpeedOverall = max($maxSpeedOverall, $maxSpeed);
                $totalAverageSpeed += $averageSpeed;
            }

            // Calculer la vitesse moyenne globale
            $globalAverageSpeed = $totalAverageSpeed / $sessionCount;

            // Créer ou mettre à jour UserPerformance
            UserPerformance::updateOrCreate(
                ['userID' => $user->id],
                [
                    'maxSpeed' => $maxSpeedOverall,
                    'totalDistance' => round($totalDistance, 2),
                    'duration' => $totalDuration,
                    'average_speed' => round($globalAverageSpeed, 2),
                    'session_id' => $sessions[0]->session_id, // Dernière session
                    'session_date' => $sessions[0]->session_date,
                ]
            );
        }

        // Créer quelques utilisateurs avec des performances exceptionnelles
        $topUsers = $users->take(3);

        foreach ($topUsers as $index => $user) {
            // Créer une session exceptionnelle
            $exceptionalMaxSpeed = fake()->randomFloat(2, 90, 120);
            $exceptionalAverageSpeed = fake()->randomFloat(2, 60, 80);
            $exceptionalDuration = fake()->numberBetween(3600, 7200); // 1-2h
            $exceptionalDistance = ($exceptionalAverageSpeed / 3.6) * $exceptionalDuration;

            PerformanceSession::create([
                'user_id' => $user->id,
                'session_id' => Str::uuid(),
                'max_speed' => $exceptionalMaxSpeed,
                'average_speed' => $exceptionalAverageSpeed,
                'distance' => round($exceptionalDistance, 2),
                'duration' => $exceptionalDuration,
                'session_date' => fake()->dateTimeBetween('-7 days', 'now'),
            ]);

            // Mettre à jour UserPerformance avec les nouvelles données
            $userPerf = UserPerformance::where('userID', $user->id)->first();
            if ($userPerf) {
                $userPerf->update([
                    'maxSpeed' => max($userPerf->maxSpeed, $exceptionalMaxSpeed),
                    'totalDistance' => $userPerf->totalDistance + $exceptionalDistance,
                    'duration' => $userPerf->duration + $exceptionalDuration,
                ]);
            }
        }

        $this->command->info('Sessions de performance créées avec succès !');
    }
}
