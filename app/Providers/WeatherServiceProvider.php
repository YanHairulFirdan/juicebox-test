<?php

namespace App\Providers;

use App\Services\Weather\Contracts\WeatherService;
use App\Services\Weather\Providers\WeatherApi;
use Illuminate\Support\ServiceProvider;

class WeatherServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->app->bind(WeatherService::class, function ($app) {
            $serviceName = config('weather.service_name', 'WeatherAPI');

            if ($serviceName !== 'WeatherAPI') {
                throw new \Exception("Unsupported weather service: {$serviceName}");
            }

            return new WeatherApi();
        });
    }
}
