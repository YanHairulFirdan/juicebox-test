<?php

namespace App\Services\Weather\Contracts;

use App\Services\Weather\Dtos\CurrentWeatherDto;

interface WeatherService
{
    public function getCurrentWeather(string $city = 'Perth'): CurrentWeatherDto;
}
