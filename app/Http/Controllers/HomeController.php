<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Anecdote;
use App\Models\Challenge;
use App\Models\ChallengeProof;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    /**
     * Obtenir l'activité la plus proche, un défi au hasard, et les contacts "Team Info".
     */
    public function getRandomData(Request $request)
    {
        try {
            $currentDate = Carbon::today();

            $closestActivity = Activity::whereDate('date', '>=', $currentDate)
                ->whereNotNull('startTime')
                ->orderBy('date', 'ASC')
                ->orderBy('startTime', 'ASC')
                ->first();

            if ($closestActivity) {
                if ($closestActivity->startTime) {
                    $closestActivity->startTime = Carbon::parse($closestActivity->startTime)->format('H\hi');
                } else {
                    $closestActivity->startTime = 'N/A';
                }

                if ($closestActivity->endTime) {
                    $closestActivity->endTime = Carbon::parse($closestActivity->endTime)->format('H\hi');
                } else {
                    $closestActivity->endTime = 'N/A';
                }
            }

            $userId = $request->user['id'];
            $roomId = User::where('id', $userId)->first()->roomID;

            $doneChallenges = ChallengeProof::where('room_id', '!=', $roomId)->inRandomOrder()->get();
            $randomChallenge = Challenge::whereNotIn('id', $doneChallenges->pluck('challenge_id'))->inRandomOrder()->first();

            $bestAnecdote = Anecdote::withCount('likes')
                ->where('valid', true)
                ->orderBy('likes_count', 'desc')
                ->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'closestActivity' => $closestActivity ? $closestActivity : null,
                    'randomChallenge' => $randomChallenge ? $randomChallenge->title : null,
                    'bestAnecdote' => $bestAnecdote ? $bestAnecdote->text : null
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => "L'application n'est pas tout à fait finie... " . $e->getMessage()]);
        }
    }

    public function getWeather(): JsonResponse
    {
        $cachePath = storage_path('app/' . 'weather.json');

        if (file_exists($cachePath)) {
            $content = json_decode(file_get_contents($cachePath), true);
            $timestamp = $content['timestamp'] ?? 0;

            if (time() - $timestamp < 10 * 60) {
                return response()->json([
                    'source' => 'cache',
                    'data' => $content['data']
                ]);
            }
        }

        $apiKey = env('WEATHER_API_KEY');
        $location = 'Pas de la Case, Andorra';

        try {
            $response = Http::get('https://api.weatherapi.com/v1/forecast.json', [
                'key' => $apiKey,
                'q' => $location,
                'lang' => 'fr',
                'days' => 1,
            ]);

            if ($response->failed()) {
                throw new \Exception('Erreur lors de la requête WeatherAPI');
            }

            $fullData = $response->json();

            // Extraction des données utilisées par l'application
            $optimizedData = $this->extractWeatherData($fullData);

            $cacheData = [
                'timestamp' => time(),
                'data' => $optimizedData,
            ];

            Storage::put('weather.json', json_encode($cacheData, JSON_PRETTY_PRINT));

            return response()->json([
                'success' => true,
                'data' => $optimizedData
            ]);

        } catch (\Exception $e) {
            if (isset($content['data'])) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Extrait uniquement les données météo utilisées par l'application React Native
     */
    private function extractWeatherData(array $fullData): array
    {
        $hourlyData = [];

        // Extraction des données horaires (seulement les champs utilisés)
        if (isset($fullData['forecast']['forecastday'][0]['hour'])) {
            foreach ($fullData['forecast']['forecastday'][0]['hour'] as $hour) {
                $hourlyData[] = [
                    'temp_c' => $hour['temp_c'],
                    'condition' => [
                        'code' => $hour['condition']['code'],
                        'text' => $hour['condition']['text']
                    ],
                    'is_day' => $hour['is_day']
                ];
            }
        }

        return [
            'location' => [
                'name' => $fullData['location']['name'] ?? null,
                'region' => $fullData['location']['region'] ?? null,
                'country' => $fullData['location']['country'] ?? null
            ],
            'current' => [
                'temp_c' => $fullData['current']['temp_c'] ?? null,
                'condition' => [
                    'code' => $fullData['current']['condition']['code'] ?? null,
                    'text' => $fullData['current']['condition']['text'] ?? null
                ],
                'is_day' => $fullData['current']['is_day'] ?? null,
                'wind_kph' => $fullData['current']['wind_kph'] ?? null,
                'humidity' => $fullData['current']['humidity'] ?? null,
                'feelslike_c' => $fullData['current']['feelslike_c'] ?? null
            ],
            'forecast' => [
                'forecastday' => [
                    [
                        'hour' => $hourlyData
                    ]
                ]
            ]
        ];
    }
}
