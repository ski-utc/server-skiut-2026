<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Anecdote;

class AnecdoteSeeder extends Seeder
{
    public function run()
    {
        Anecdote::truncate();
        $anecdotes = [
            [ # en attente
                'text' => 'louise a mangé une pomme hier soir !',
                'room' => '52',
                'userId' => 316050,
                'valid' => false,
                'alert' => 0,
                'delete' => false,
                'active' => false
            ],
            [ # signalée 
                'text' => 'Anecdote 2',
                'room' => '53',
                'userId' => 316366,
                'valid' => true,
                'alert' => 1,
                'delete' => false,
                'active' => true
            ],
            [ # en attente + signalée
                'text' => 'Anecdote 3',
                'room' => '52',
                'userId' => 316050,
                'valid' => false,
                'alert' => 2,
                'delete' => false,
                'active' => false
            ],
            [ # supprimée 
                'text' => 'Anecdote 4',
                'room' => '52',
                'userId' => 316050,
                'valid' => true,
                'alert' => 0,
                'delete' => true,
                'active' => false
            ],
            [ # signalée
                'text' => 'Anecdote 5',
                'room' => '53',
                'userId' => 316366,
                'valid' => true,
                'alert' => 3,
                'delete' => false,
                'active' => true
            ],
            [ # en attente 
                'text' => 'Anecdote 6',
                'room' => '53',
                'userId' => 316366,
                'valid' => false,
                'alert' => 0,
                'delete' => false,
                'active' => false
            ],
            [ # en attente
                'text' => 'Anecdote 7',
                'room' => '53',
                'userId' => 316366,
                'valid' => false,
                'alert' => 0,
                'delete' => false,
                'active' => false
            ],
            [ # toutes
                'text' => 'Anecdote 8',
                'room' => '52',
                'userId' => 316050,
                'valid' => true,
                'alert' => 0,
                'delete' => false,
                'active' => true
            ],
            [ # signalée
                'text' => 'Anecdote 9',
                'room' => '53',
                'userId' => 316366,
                'valid' => false,
                'alert' => 4,
                'delete' => false,
                'active' => false
            ],
            [ # toutes
                'text' => 'Anecdote 10',
                'room' => '53',
                'userId' => 316366,
                'valid' => true,
                'alert' => 0,
                'delete' => false,
                'active' => true
            ],
            [ # toutes
                'text' => 'Anecdote 11',
                'room' => '53',
                'userId' => 316366,
                'valid' => true,
                'alert' => 0,
                'delete' => false,
                'active' => true
            ],
        ];
        Anecdote::insert($anecdotes);
    }
}
