<?php

namespace App\Checks;

use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class MemoryUsageCheck extends Check
{
    public function run(): Result
    {
        $result = Result::make();

        $used = memory_get_usage(true);
        $peak = memory_get_peak_usage(true);
        $limit = ini_get('memory_limit');

        $limitBytes = $this->convertToBytes($limit);

        $usedPercent = ($used / $limitBytes) * 100;
        $peakPercent = ($peak / $limitBytes) * 100;

        $label = sprintf(
            'Memory: %s used (%.1f%%), peak: %s (%.1f%%), limit: %s',
            $this->formatBytes($used),
            $usedPercent,
            $this->formatBytes($peak),
            $peakPercent,
            $limit
        );

        if ($usedPercent > 90) {
            return $result->failed($label);
        }

        if ($usedPercent > 75) {
            return $result->warning($label);
        }

        return $result->ok($label);
    }

    private function formatBytes($bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . $units[$pow];
    }

    private function convertToBytes($value): int
    {
        $value = trim($value);
        $last = strtolower($value[strlen($value) - 1]);

        switch ($last) {
            case 'g':
                $value = (int)$value * 1024;
            case 'm':
                $value = (int)$value * 1024;
            case 'k':
                $value = (int)$value * 1024;
                break;
            default:
                $value = (int)$value;
        }

        return $value;
    }
}
