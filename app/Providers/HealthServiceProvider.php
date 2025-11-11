<?php

namespace App\Providers;

use App\Checks\ApiResponseCheck;
use App\Checks\DiskSpaceCheck;
// use Spatie\Health\Checks\Checks\OptimizedAppCheck;
// use Spatie\Health\Checks\Checks\DebugModeCheck;
// use Spatie\Health\Checks\Checks\EnvironmentCheck;
use App\Checks\MemoryUsageCheck;
use App\Checks\StorageCheck;
// use Spatie\Health\Checks\Checks\QueueCheck;
// use Spatie\Health\Checks\Checks\ScheduleCheck;
use Illuminate\Support\ServiceProvider;
use Spatie\Health\Checks\Checks\CacheCheck;
use Spatie\Health\Checks\Checks\DatabaseCheck;
use Spatie\Health\Facades\Health;

class HealthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Health::checks([
            // OptimizedAppCheck::new(),
            // DebugModeCheck::new(),
            // EnvironmentCheck::new(),
            DatabaseCheck::new(),
            CacheCheck::new(),
            // QueueCheck::new(),
            // ScheduleCheck::new(),
            DiskSpaceCheck::new(),
            MemoryUsageCheck::new(),
            StorageCheck::new(),
            ApiResponseCheck::new(),
        ]);
    }
}
