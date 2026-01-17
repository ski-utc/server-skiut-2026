<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\APC;
use Prometheus\Storage\InMemory;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Events\QueryExecuted;
class PrometheusServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app->singleton(CollectorRegistry::class, function () {
            if (app()->environment('testing')) {
                return new CollectorRegistry(new InMemory());
            }
            return new CollectorRegistry(new APC());
        });
    }
    /**
     * Bootstrap services.
     */
    public function boot()
    {
        $registry = $this->app->make(CollectorRegistry::class);

        DB::listen(function (QueryExecuted $query) use ($registry) {
            $operation = 'other';
            if (preg_match('/^(\w+)/', $query->sql, $matches)) {
                $operation = strtolower($matches[1]);
            }

            $histogram = $registry->getOrRegisterHistogram(
                'app',
                'db_query_duration_seconds',
                'Database query duration in seconds',
                ['operation'],
                [0.001, 0.005, 0.01, 0.05, 0.1, 0.5, 1, 5]
            );

            $histogram->observe($query->time / 1000, [$operation]);

            $counter = $registry->getOrRegisterCounter(
                'app',
                'db_queries_total',
                'Total database queries',
                ['operation']
            );

            $counter->inc([$operation]);
        });
    }
}
