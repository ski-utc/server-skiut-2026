<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersSeeder extends Seeder
{
    public function run()
    {
        $users = [ 
            ['id' => 317864, 'cas' => 'rbaudoin', 'firstName' => 'Robin', 'lastName' => 'Baudoin', 'email' => 'robin.baudoin@etu.utc.fr', 'password' => null, 'roomID' => 2, 'location' => null, 'admin' => false],
            ['id' => 316449, 'cas' => 'pabrunel', 'firstName' => 'Paul', 'lastName' => 'BRUNEL', 'email' => 'paul.brunel@etu.utc.fr', 'password' => null, 'roomID' => 2, 'location' => null, 'admin' => false],
            ['id' => 316625, 'cas' => 'rapusset', 'firstName' => 'Raphaël', 'lastName' => 'Pusset', 'email' => 'raphael.pusset@etu.utc.fr', 'password' => null, 'roomID' => 2, 'location' => null, 'admin' => false],
            ['id' => 317297, 'cas' => 'comartin', 'firstName' => 'Colas', 'lastName' => 'MARTIN', 'email' => 'colas.martin@etu.utc.fr', 'password' => null, 'roomID' => 2, 'location' => null, 'admin' => false],
            ['id' => 316450, 'cas' => 'rebasiuk', 'firstName' => 'Rémi', 'lastName' => 'Basiuk', 'email' => 'remi.basiuk@etu.utc.fr', 'password' => null, 'roomID' => 63, 'location' => null, 'admin' => false],
            ['id' => 328046, 'cas' => 'cfrançoi', 'firstName' => 'Charles', 'lastName' => 'François', 'email' => 'charles.francois@etu.utc.fr', 'password' => null, 'roomID' => 63, 'location' => null, 'admin' => false],
            ['id' => 316497, 'cas' => 'vtessier', 'firstName' => 'Valentin', 'lastName' => 'Tessier', 'email' => 'valentin.tessier@etu.utc.fr', 'password' => null, 'roomID' => 63, 'location' => null, 'admin' => false],
            ['id' => 316448, 'cas' => 'jvaucher', 'firstName' => 'Julie', 'lastName' => 'Vaucher', 'email' => 'julie.vaucher@etu.utc.fr', 'password' => null, 'roomID' => 63, 'location' => null, 'admin' => false],
            ['id' => 317857, 'cas' => 'louahmar', 'firstName' => 'Louna', 'lastName' => 'Ahmar', 'email' => 'louna.ahmar@etu.utc.fr', 'password' => null, 'roomID' => 86, 'location' => null, 'admin' => false],
            ['id' => 317858, 'cas' => 'jlagorss', 'firstName' => 'Jean', 'lastName' => 'Lagorsse', 'email' => 'jean.lagorsse@etu.utc.fr', 'password' => null, 'roomID' => 86, 'location' => null, 'admin' => false],
            ['id' => 317838, 'cas' => 'lrecoupe', 'firstName' => 'Loïc', 'lastName' => 'Récoupé', 'email' => 'loic.recoupe@etu.utc.fr', 'password' => null, 'roomID' => 86, 'location' => null, 'admin' => false],
            ['id' => 317430, 'cas' => 'aventura', 'firstName' => 'Alexandre', 'lastName' => 'Ventura', 'email' => 'alexandre.ventura@etu.utc.fr', 'password' => null, 'roomID' => 86, 'location' => null, 'admin' => false],
            ['id' => 317193, 'cas' => 'pdecrooc', 'firstName' => 'Pierre', 'lastName' => 'Decroocq', 'email' => 'pierre.decroocq@etu.utc.fr', 'password' => null, 'roomID' => 8, 'location' => null, 'admin' => false],
            ['id' => 317164, 'cas' => 'mardonze', 'firstName' => 'Martin', 'lastName' => 'Donze', 'email' => 'martin.donze@etu.utc.fr', 'password' => null, 'roomID' => 8, 'location' => null, 'admin' => false],
            ['id' => 316485, 'cas' => 'agodoype', 'firstName' => 'Antonio', 'lastName' => 'GODOY PELLINI', 'email' => 'antonio.godoy-pellini@etu.utc.fr', 'password' => null, 'roomID' => 8, 'location' => null, 'admin' => false],
            ['id' => 317241, 'cas' => 'xlemerle', 'firstName' => 'Xavier', 'lastName' => 'Lemerle', 'email' => 'xavier.lemerle@etu.utc.fr', 'password' => null, 'roomID' => 8, 'location' => null, 'admin' => false],
            ['id' => 317052, 'cas' => 'elcherel', 'firstName' => 'Elliot', 'lastName' => 'Cherel', 'email' => 'elliot.cherel@etu.utc.fr', 'password' => null, 'roomID' => 40, 'location' => null, 'admin' => false],
            ['id' => 317146, 'cas' => 'vidubois', 'firstName' => 'Victor', 'lastName' => 'Dubois', 'email' => 'victor.dubois@etu.utc.fr', 'password' => null, 'roomID' => 40, 'location' => null, 'admin' => false],
            ['id' => 316773, 'cas' => 'julhenry', 'firstName' => 'Jules', 'lastName' => 'Henry', 'email' => 'jules.henry@etu.utc.fr', 'password' => null, 'roomID' => 40, 'location' => null, 'admin' => false],
            ['id' => 317126, 'cas' => 'jriviere', 'firstName' => 'Jules', 'lastName' => 'Riviere', 'email' => 'jules.riviere@etu.utc.fr', 'password' => null, 'roomID' => 40, 'location' => null, 'admin' => false],
            ['id' => 316629, 'cas' => 'abrevier', 'firstName' => 'Antoine', 'lastName' => 'Breviere', 'email' => 'antoine.breviere@etu.utc.fr', 'password' => null, 'roomID' => 51, 'location' => null, 'admin' => false],
            ['id' => 317876, 'cas' => 'tde boni', 'firstName' => 'Theo', 'lastName' => 'De Bonis', 'email' => 'theo.de-bonis@etu.utc.fr', 'password' => null, 'roomID' => 51, 'location' => null, 'admin' => false],
            ['id' => 317751, 'cas' => 'tde talh', 'firstName' => 'Théophile', 'lastName' => 'De Talhouet', 'email' => 'theophile.de-talhouet@etu.utc.fr', 'password' => null, 'roomID' => 51, 'location' => null, 'admin' => false],
            ['id' => 316626, 'cas' => 'vletelli', 'firstName' => 'VALENTINE', 'lastName' => 'LETELLIER', 'email' => 'valentine.letellier@etu.utc.fr', 'password' => null, 'roomID' => 51, 'location' => null, 'admin' => false],
            ['id' => 317369, 'cas' => 'lambrosi', 'firstName' => 'Loric', 'lastName' => 'Ambrosioni', 'email' => 'loric.ambrosioni@etu.utc.fr', 'password' => null, 'roomID' => 25, 'location' => null, 'admin' => false],
            ['id' => 317032, 'cas' => 'mleonard', 'firstName' => 'Manuel', 'lastName' => 'Leonard', 'email' => 'manuel.leonard@etu.utc.fr', 'password' => null, 'roomID' => 25, 'location' => null, 'admin' => false],
            ['id' => 316640, 'cas' => 'snoire', 'firstName' => 'Sylvain', 'lastName' => 'Noire', 'email' => 'sylvain.noire@etu.utc.fr', 'password' => null, 'roomID' => 25, 'location' => null, 'admin' => false],
            ['id' => 316779, 'cas' => 'gbonfils', 'firstName' => 'Gabriel', 'lastName' => 'Bonfils', 'email' => 'gabriel.bonfils@etu.utc.fr', 'password' => null, 'roomID' => 25, 'location' => null, 'admin' => false],
            ['id' => 317115, 'cas' => 'dauxthd', 'firstName' => 'David', 'lastName' => 'Dauxthie', 'email' => 'david.dauxthie@etu.utc.fr', 'password' => null, 'roomID' => 25, 'location' => null, 'admin' => false]
        ]; 
            

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
