<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\APC; # TODO  remove APC to only use InMemory
use Prometheus\Storage\InMemory;

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
            return new CollectorRegistry(new APC()); # TODO  remove APC to only use InMemory
        });
    }
}
