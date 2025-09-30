<?php

namespace App\Jobs;

use App\Models\Weather;
use App\Services\Weather\Contracts\WeatherService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class UpdateWeatherData implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(WeatherService $weatherService): void
    {
        try {
            $condition = $weatherService->getCurrentWeather('Perth');

            Cache::put('current_weather_perth', $condition->toArray(), now()->addMinutes(15));
        } catch (\Throwable $th) {
            report($th);
            return;
        }
    }
}
