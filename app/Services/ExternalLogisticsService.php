<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class ExternalLogisticsService
{
    /**
     * WMO Weather interpretation codes (WW).
     */
    protected const WEATHER_CODE_MAP = [
        0 => 'Clear sky',
        1 => 'Mainly clear',
        2 => 'Partly cloudy',
        3 => 'Overcast',
        45 => 'Foggy',
        48 => 'Depositing rime fog',
        51 => 'Light drizzle',
        53 => 'Moderate drizzle',
        55 => 'Dense drizzle',
        56 => 'Light freezing drizzle',
        57 => 'Dense freezing drizzle',
        61 => 'Slight rain',
        63 => 'Moderate rain',
        65 => 'Heavy rain',
        66 => 'Light freezing rain',
        67 => 'Heavy freezing rain',
        71 => 'Slight snowfall',
        73 => 'Moderate snowfall',
        75 => 'Heavy snowfall',
        77 => 'Snow grains',
        80 => 'Slight rain showers',
        81 => 'Moderate rain showers',
        82 => 'Violent rain showers',
        85 => 'Slight snow showers',
        86 => 'Heavy snow showers',
        95 => 'Thunderstorm',
        96 => 'Thunderstorm with slight hail',
        99 => 'Thunderstorm with heavy hail',
    ];

    /**
     * Retrieve real-time operational weather for a given GPS coordinate from Open-Meteo API.
     *
     * @param float $latitude
     * @param float $longitude
     * @return array
     */
    public function getLocationWeather(float $latitude, float $longitude): array
    {
        $fallback = [
            'success' => false,
            'temperature' => null,
            'windspeed' => null,
            'weathercode' => null,
            'summary' => 'Weather data temporarily unavailable',
            'latitude' => $latitude,
            'longitude' => $longitude,
        ];

        try {
            $response = Http::timeout(5)
                ->get('https://api.open-meteo.com/v1/forecast', [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'current_weather' => 'true',
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $current = $data['current_weather'] ?? null;

                if ($current && isset($current['temperature'])) {
                    $code = (int) ($current['weathercode'] ?? -1);
                    $summary = self::WEATHER_CODE_MAP[$code] ?? 'Moderate conditions';

                    return [
                        'success' => true,
                        'temperature' => (float) $current['temperature'],
                        'windspeed' => (float) ($current['windspeed'] ?? 0),
                        'weathercode' => $code,
                        'summary' => $summary,
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                    ];
                }
            }

            Log::warning("Open-Meteo returned unsuccessful status: " . $response->status());
            return $fallback;
        } catch (Throwable $e) {
            Log::warning("ExternalLogisticsService::getLocationWeather error: " . $e->getMessage());
            return $fallback;
        }
    }

    /**
     * Geocode location query (port name or city) via OpenStreetMap Nominatim API.
     *
     * @param string $query
     * @return array|null ['latitude' => float, 'longitude' => float, 'display_name' => string]
     */
    public function geocodeLocation(string $query): ?array
    {
        $trimmed = trim($query);
        if ($trimmed === '') {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'CargoTrace-Academic-Project/1.0',
            ])
            ->timeout(5)
            ->get('https://nominatim.openstreetmap.org/search', [
                'q' => $trimmed,
                'format' => 'json',
                'limit' => 1,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (is_array($data) && count($data) > 0 && isset($data[0]['lat'], $data[0]['lon'])) {
                    return [
                        'latitude' => (float) $data[0]['lat'],
                        'longitude' => (float) $data[0]['lon'],
                        'display_name' => (string) ($data[0]['display_name'] ?? $trimmed),
                    ];
                }
            }

            return null;
        } catch (Throwable $e) {
            Log::warning("ExternalLogisticsService::geocodeLocation error: " . $e->getMessage());
            return null;
        }
    }
}
