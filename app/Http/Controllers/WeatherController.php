<?php

namespace App\Http\Controllers;

use App\Models\Weather;
use App\Services\Weather\Contracts\WeatherService;
use Illuminate\Support\Facades\Cache;

class WeatherController extends Controller
{
    public function show(WeatherService $weatherService): \Illuminate\Http\JsonResponse
    {
        $city = 'Perth';
        $cacheKey = 'current_weather_' . strtolower($city);

        $weather = Cache::get($cacheKey);

        if (!$weather) {
            try {
                $weather = $weatherService->getCurrentWeather($city);
            } catch (\Throwable $th) {
                return response()->json([
                    'message' => 'Failed to fetch weather data.',
                    'error' => $th->getMessage(),
                ], 500);
            }

            Cache::put($cacheKey, $weather->toArray(), now()->addMinutes(15));
        }

        return response()->json([
            'data' => $weather,
        ]);
    }
}
