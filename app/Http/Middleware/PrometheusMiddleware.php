<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use PHPUnit\Runner\DeprecationCollector\Collector;
use Prometheus\CollectorRegistry;

class PrometheusMiddleware
{
    private CollectorRegistry $registry;
    private $counter;
    private $histogram;
    private $exceptionCounter;

    public function __construct(CollectorRegistry $registry)
    {
        $this->registry = $registry;

        $this->counter = $registry->getOrRegisterCounter(
            'app',
            'http_requests_total',
            'Total number of HTTP requests',
            ['status', 'path', 'method']
        );

        $this->histogram = $registry->getOrRegisterHistogram(
            'app',
            'http_request_duration_seconds',
            'Duration of HTTP requests in seconds',
            ['status', 'path', 'method'],
            [0.1, 0.3, 1.5, 10.0]
        );

        $memoryGauge = $registry->getOrRegisterGauge(
            'app',
            'php_memory_usage_bytes',
            'PHP memory usage'
        );
        $memoryGauge->set(memory_get_usage());

        $this->exceptionCounter = $registry->getOrRegisterCounter(
            'app', 
            'exceptions_total', 
            'Total number of exceptions'
        );
    }

    public function handle(Request $request, Closure $next)
    {
        $start = microtime(true);

        try {
            $response = $next($request);
        } catch (\Throwable $e) {
            $this->exceptionCounter->inc();
            throw $e;
        }

        $duration = microtime(true) - $start;

        $this->counter->inc([
            'status' => $response->getStatusCode(),
            'path' => $request->path(),
            'method' => $request->method()
        ]);

        $this->histogram->observe($duration, [
            'status' => $response->getStatusCode(),
            'path' => $request->path(),
            'method' => $request->method()
        ]);

        return $response;
    }
}
