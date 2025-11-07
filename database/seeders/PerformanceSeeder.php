<?php

namespace Database\Seeders;

use App\Models\PerformanceSession;
use App\Models\User;
use App\Models\UserPerformance;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PerformanceSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::limit(15)->get();

        if ($users->isEmpty()) {
            $this->command->warn('Aucun utilisateur trouvé pour créer les performances');
            return;
        }

        foreach ($users as $user) {
            $sessionCount = fake()->numberBetween(3, 12);
            $sessions = [];
            $totalDistance = 0;
            $totalDuration = 0;
            $maxSpeedOverall = 0;
            $totalAverageSpeed = 0;

            $userPerformance = UserPerformance::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'max_speed' => 0,
                    'total_distance' => 0,
                    'duration' => 0,
                    'average_speed' => 0,
                ]
            );

            for ($i = 0; $i < $sessionCount; $i++) {
                $maxSpeed = fake()->randomFloat(2, 15, 85);
                $averageSpeed = fake()->randomFloat(2, 10, $maxSpeed * 0.8);
                $duration = fake()->numberBetween(600, 14400);
                $distance = ($averageSpeed / 3.6) * $duration;

                $session = PerformanceSession::create([
                    'user_performance_id' => $userPerformance->id,
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

            $globalAverageSpeed = $totalAverageSpeed / $sessionCount;

            $userPerformance->update([
                'max_speed' => $maxSpeedOverall,
                'total_distance' => round($totalDistance, 2),
                'duration' => $totalDuration,
                'average_speed' => round($globalAverageSpeed, 2),
                'session_id' => $sessions[0]->session_id,
                'session_date' => $sessions[0]->session_date,
            ]);
        }

        $topUsers = $users->take(3);

        foreach ($topUsers as $index => $user) {
            $exceptionalMaxSpeed = fake()->randomFloat(2, 90, 120);
            $exceptionalAverageSpeed = fake()->randomFloat(2, 60, 80);
            $exceptionalDuration = fake()->numberBetween(3600, 7200);
            $exceptionalDistance = ($exceptionalAverageSpeed / 3.6) * $exceptionalDuration;

            $userPerf = UserPerformance::where('user_id', $user->id)->first();
            if ($userPerf) {
                PerformanceSession::create([
                    'user_performance_id' => $userPerf->id,
                    'session_id' => Str::uuid(),
                    'max_speed' => $exceptionalMaxSpeed,
                    'average_speed' => $exceptionalAverageSpeed,
                    'distance' => round($exceptionalDistance, 2),
                    'duration' => $exceptionalDuration,
                    'session_date' => fake()->dateTimeBetween('-7 days', 'now'),
                ]);

                $userPerf->update([
                    'max_speed' => max($userPerf->max_speed, $exceptionalMaxSpeed),
                    'total_distance' => $userPerf->total_distance + $exceptionalDistance,
                    'duration' => $userPerf->duration + $exceptionalDuration,
                ]);
            }
        }
    }
}
