<?php

namespace Database\Seeders;

use App\Models\PushToken;
use App\Models\User;
use Illuminate\Database\Seeder;

class PushTokenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('Aucun utilisateur trouvé pour créer les push tokens');
            return;
        }

        $usersWithTokens = $users->random(min((int)($users->count() * 0.7), $users->count()));
        foreach ($usersWithTokens as $user) {
            $tokenCount = fake()->numberBetween(1, 2);

            for ($i = 0; $i < $tokenCount; $i++) {
                PushToken::factory()->create([
                    'user_id' => $user->id,
                ]);
            }
        }
    }
}
