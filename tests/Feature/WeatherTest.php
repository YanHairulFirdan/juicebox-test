<?php

namespace Tests\Unit\Services;

use App\Services\Weather\Contracts\WeatherService;
use App\Services\Weather\Dtos\CurrentWeatherDto;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WeatherServiceTest extends TestCase
{
    public function test_service_returns_current_weather_dto()
    {
        Http::fake([
            'api.weatherapi.com/*' => Http::response([
                'location' => [
                    'name' => 'Perth',
                    'region' => 'Western Australia',
                    'country' => 'Australia',
                    'localtime' => '2023-10-01 12:00',
                ],
                'current' => [
                    'temp_c' => 25,
                    'temp_f' => 77,
                    'condition' => ['text' => 'Sunny', 'icon' => 'icon.png'],
                    'feelslike_c' => 26,
                    'feelslike_f' => 78.8,
                    'humidity' => 40,
                    'wind_kph' => 15,
                    'wind_dir' => 'E',
                    'uv' => 7,
                    'vis_km' => 10,
                    'last_updated' => '2023-10-01 11:45',
                ]
            ], 200),
        ]);

        /** @var WeatherService $service */
        $service = $this->app->make(WeatherService::class);
        $dto = $service->getCurrentWeather('Perth');

        $this->assertInstanceOf(CurrentWeatherDto::class, $dto);
        $this->assertEquals('Perth', $dto->city);
        $this->assertEquals('Western Australia', $dto->region);
        $this->assertEquals('Australia', $dto->country);
        $this->assertEquals(25, $dto->temperature_c);
        $this->assertEquals('Sunny', $dto->condition);
    }

    public function test_service_throws_exception_on_http_failure()
    {
        Http::fake([
            'api.weatherapi.com/*' => Http::response(null, 500),
        ]);

        $this->expectException(\Exception::class);

        /** @var WeatherService $service */
        $service = $this->app->make(WeatherService::class);
        $service->getCurrentWeather('Perth');
    }

    public function test_weather_endpoint_returns_successful_response()
    {
        Http::fake([
            'api.weatherapi.com/*' => Http::response([
                'location' => [
                    'name' => 'Perth',
                    'region' => 'Western Australia',
                    'country' => 'Australia',
                    'localtime' => '2023-10-01 12:00',
                ],
                'current' => [
                    'temp_c' => 25,
                    'temp_f' => 77,
                    'condition' => ['text' => 'Sunny', 'icon' => 'icon.png'],
                    'feelslike_c' => 26,
                    'feelslike_f' => 78.8,
                    'humidity' => 40,
                    'wind_kph' => 15,
                    'wind_dir' => 'E',
                    'uv' => 7,
                    'vis_km' => 10,
                    'last_updated' => '2023-10-01 11:45',
                ]
            ], 200),
        ]);
        $response = $this->getJson('/api/weather');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'city',
                'region',
                'country',
                'temperature_c',
                'temperature_f',
                'condition',
                'condition_icon',
                'feels_like_c',
                'feels_like_f',
                'humidity',
                'wind_kph',
                'wind_dir',
                'uv_index',
                'visibility_km',
                'last_updated',
                'local_time',
            ],
        ]);
        $this->assertNotEmpty($response->json('data'));
        $this->assertEquals('Perth', $response->json('data.city'));
    }

    // test the error handling when the external API fails
    public function test_weather_endpoint_handles_service_exception()
    {
        Http::fake([
            'api.weatherapi.com/*' => Http::response(null, 500),
        ]);
        $response = $this->getJson('/api/weather');
        $response->assertStatus(500);
        $response->assertJson([
            'error' => 'Unable to fetch weather data at this time.',
        ]);
        $this->assertArrayHasKey('error', $response->json());
    }
}
