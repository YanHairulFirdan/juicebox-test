<?php

return [
    'service_name' => env('WEATHER_SERVICE_NAME', 'WeatherAPI'),
    'base_uri' => env('WEATHER_SERVICE_BASE_URI', 'https://api.weather.com/v3/'),
    'api_key' => env('WEATHER_SERVICE_API_KEY'),
];
