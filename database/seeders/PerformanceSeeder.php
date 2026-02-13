<?php

namespace Database\Seeders;

use App\Models\PerformanceSession;
use App\Models\User;
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
            $sessionCount = fake()->numberBetween(3, 12);

            for ($i = 0; $i < $sessionCount; $i++) {
                $maxSpeed = fake()->randomFloat(2, 15, 85);
                $averageSpeed = fake()->randomFloat(2, 10, $maxSpeed * 0.8);
                $duration = fake()->numberBetween(600, 14400);
                $distance = ($averageSpeed / 3.6) * $duration;

                PerformanceSession::create([
                    'user_id' => $user->id,
                    'session_id' => Str::uuid(),
                    'max_speed' => $maxSpeed,
                    'average_speed' => $averageSpeed,
                    'distance' => round($distance, 2),
                    'duration' => $duration,
                    'session_date' => fake()->dateTimeBetween('-30 days', 'now'),
                ]);
            }
        }

        $topUsers = $users->take(3);

        foreach ($topUsers as $user) {
            $exceptionalMaxSpeed = fake()->randomFloat(2, 90, 120);
            $exceptionalAverageSpeed = fake()->randomFloat(2, 60, 80);
            $exceptionalDuration = fake()->numberBetween(3600, 7200);
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
        }
    }
}
