<?php

namespace App\Services\Weather\Dtos;

use Spatie\LaravelData\Data;

final class CurrentWeatherDto extends Data
{
    public function __construct(
        public string $city,
        public string $region,
        public string $country,
        public float $temperature_c,
        public float $temperature_f,
        public string $condition,
        public string $condition_icon,
        public float $feels_like_c,
        public float $feels_like_f,
        public int $humidity,
        public float $wind_kph,
        public string $wind_dir,
        public float $uv_index,
        public float $visibility_km,
        public string $last_updated,
        public string $local_time,
    ) {}
}
