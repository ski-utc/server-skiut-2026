<?php

namespace App\Checks;

use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class DiskSpaceCheck extends Check
{
    public function run(): Result
    {
        $result = Result::make();

        $disk = disk_free_space('/');
        $total = disk_total_space('/');
        $percentFree = ($disk / $total) * 100;

        $label = 'Disk space usage: ' . $this->formatBytes($total - $disk) . ' / ' . $this->formatBytes($total) . ' (' . round($percentFree, 2) . '% free)';

        if ($percentFree < 10) {
            return $result->failed($label);
        }

        if ($percentFree < 20) {
            return $result->warning($label);
        }

        return $result->ok($label);
    }

    private function formatBytes($bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
