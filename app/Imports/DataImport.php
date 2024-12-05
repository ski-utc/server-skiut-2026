<?php

namespace App\Imports;

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Room;
use Maatwebsite\Excel\Concerns\ToModel;
//use Maatwebsite\Excel\Concerns\WithHeadingRow;


class DataImport implements ToModel
{
    /**
     * Transform each row into a model.
     *
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */

    public function model(array $row)
    {
        DB::statement('PRAGMA foreign_keys = OFF');
        return new User([
            'id' => $row[0], 
            'cas' => $row[1],
            'firstName' => $row[2], // Match Excel header case
            'lastName' => $row[3],
            'email' => $row[4],
            'password' => $row[5], // Hash this if needed
            'roomID' => $row[6], // Ensure roomID exists in rooms table
            'location' => $row[7],
            'admin' => filter_var($row[8], FILTER_VALIDATE_BOOLEAN), // Convert to boolean
            'alumniOrExte' => filter_var($row[9], FILTER_VALIDATE_BOOLEAN), // Convert to boolean
        ]);
        DB::statement('PRAGMA foreign_keys = ON');
    }
}
