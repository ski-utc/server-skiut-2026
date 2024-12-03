<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersSeeder extends Seeder
{
    public function run()
    {
        $users = [ 

        ]; 
            

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
