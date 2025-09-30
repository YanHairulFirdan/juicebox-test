<?php

namespace App\Services\Weather\Providers;

use App\Services\Weather\Contracts\WeatherService;
use App\Services\Weather\Dtos\CurrentWeatherDto;
use Illuminate\Support\Facades\Http;

class WeatherApi implements WeatherService
{
    public function getCurrentWeather(string $city = 'Perth'): CurrentWeatherDto
    {
        $config = config('weather-service');
        $query = [
            'key' => $config['api_key'],
            'q' => $city,
        ];

        $queryEncoded = http_build_query($query);
        $fullUrl      = $config['base_uri'] . '/current.json?' . $queryEncoded;

        try {
            $response = Http::send('GET', $fullUrl);
        } catch (\Throwable $th) {
            throw $th;
        }

        $data = $response->json();

        return new CurrentWeatherDto(
            city: $data['location']['name'],
            region: $data['location']['region'],
            country: $data['location']['country'],
            temperature_c: $data['current']['temp_c'],
            temperature_f: $data['current']['temp_f'],
            condition: $data['current']['condition']['text'],
            condition_icon: $data['current']['condition']['icon'],
            feels_like_c: $data['current']['feelslike_c'],
            feels_like_f: $data['current']['feelslike_f'],
            humidity: $data['current']['humidity'],
            wind_kph: $data['current']['wind_kph'],
            wind_dir: $data['current']['wind_dir'],
            uv_index: $data['current']['uv'],
            visibility_km: $data['current']['vis_km'],
            last_updated: $data['current']['last_updated'],
            local_time: $data['location']['localtime'],
        );
    }
}
