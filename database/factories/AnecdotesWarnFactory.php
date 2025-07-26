<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AnecdotesWarn>
 */
class AnecdotesWarnFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $index = 0;    // index statique pour éviter les doublons
        $maxUsers = 30;
        $maxAnecdotes = 50;
        
        $user_id = ($index % $maxUsers) + 1;
        $anecdote_id = (int)($index / $maxUsers) + 1;

        if ($anecdote_id > $maxAnecdotes) {       // Si on a utilisé toutes les combinaisons possibles, on arrête
            $anecdote_id = fake()->numberBetween(1, $maxAnecdotes);
        }
        
        $index++;

        return [
            'user_id' => $user_id,
            'anecdote_id' => $anecdote_id,
        ];
    }
} 