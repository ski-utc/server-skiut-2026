<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class InstallDependencies extends Command
{
    protected $signature = 'install:dependencies';
    protected $description = 'Install dependencies via Composer';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $process = new Process(['composer', 'install']);
        $process->setWorkingDirectory(base_path());

        try {
            $process->mustRun();
            $this->info('Dependencies installed successfully!');
        } catch (ProcessFailedException $exception) {
            $this->error('Error installing dependencies: ' . $exception->getMessage());
        }
    }
}
