<?php

namespace App\Checks;

use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class StorageCheck extends Check
{
    public function run(): Result
    {
        $result = Result::make();

        try {
            $storagePath = storage_path();
            $logPath = storage_path('logs');

            if (!is_writable($storagePath)) {
                return $result->failed("Storage directory is not writable: {$storagePath}");
            }

            if (!is_writable($logPath)) {
                return $result->failed("Logs directory is not writable: {$logPath}");
            }

            $testFile = storage_path('logs/.health-check-' . uniqid());
            if (@file_put_contents($testFile, 'test') === false) {
                return $result->failed('Cannot write to logs directory');
            }
            @unlink($testFile);

            return $result->ok('Storage directories are writable');
        } catch (\Exception $e) {
            return $result->failed('Storage check failed: ' . $e->getMessage());
        }
    }
}
