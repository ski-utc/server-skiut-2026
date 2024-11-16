<?php

namespace App\Imports;

use App\Models\Challenge;
use Maatwebsite\Excel\Concerns\ToModel;

class ChallengesImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Challenge([
            'title' => $row[0],  
            'nbPoints' => $row[1],  
        ]);
    }
}
