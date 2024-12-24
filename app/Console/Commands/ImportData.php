<?php

// fichier pour importer userImport (données des utilisateurs) dans la table users
// doit d'abord lancer le RoomsSeeder avec les userID à null pour pouvoir importer les users (car foreign key vers rooms)

namespace App\Console\Commands;

use App\Imports\DataImport; // Import the DataImport class
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class ImportData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:data {file}'; // Takes file path as an argument

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data from an Excel file into the database';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $filePath = $this->argument('file'); // Get the file path from the command argument

        // Check if the file exists
        if (!file_exists($filePath)) {
            $this->error('File does not exist.');
            return;
        }

        // Import the data from the Excel file
        Excel::import(new DataImport, $filePath);

        $this->info('Data imported successfully!');
    }
}
