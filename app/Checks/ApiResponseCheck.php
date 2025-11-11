<?php

namespace App\Checks;

use Illuminate\Support\Facades\Log;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class ApiResponseCheck extends Check
{
    public function run(): Result
    {
        $result = Result::make();

        try {
            Log::info('API URL: ' . env('APP_URL') . '/health');
            $response = @file_get_contents(
                env('APP_URL') . '/health',
                false,
                stream_context_create([
                    'http' => [
                        'timeout' => 5,
                        'method' => 'GET'
                    ]
                ])
            );

            if ($response === false) {
                return $result->failed('API not responding to health checks');
            }

            return $result->ok('API responding correctly');
        } catch (\Exception $e) {
            return $result->failed('API health check failed: ' . $e->getMessage());
        }
    }
}
