<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YandexGeocodeService
{
    private const API_URL = 'https://geocode-maps.yandex.ru/1.x/';

    public function isConfigured(): bool
    {
        return ! empty(config('services.yandex.geocoder_key'));
    }

    /**
     * Address text -> point. Returns
     * ['lat' => float, 'lng' => float, 'name' => string, 'address' => string]
     * or null when nothing is found or the geocoder is unavailable.
     */
    public function geocode(string $address): ?array
    {
        $geoObject = $this->request($address);

        return $geoObject ? $this->toPoint($geoObject) : null;
    }

    /**
     * Point -> nearest house address.
     */
    public function reverse(float $lat, float $lng): ?array
    {
        $geoObject = $this->request($lng.','.$lat, ['kind' => 'house']);

        return $geoObject ? $this->toPoint($geoObject) : null;
    }

    private function request(string $geocode, array $extra = []): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $response = Http::get(self::API_URL, array_merge([
                'apikey' => config('services.yandex.geocoder_key'),
                'geocode' => $geocode,
                'format' => 'json',
                'results' => 1,
                'lang' => 'ru_RU',
            ], $extra));
        } catch (\Throwable $e) {
            Log::warning('Yandex geocoder request failed', ['error' => $e->getMessage()]);

            return null;
        }

        if ($response->failed()) {
            Log::warning('Yandex geocoder returned an error', ['status' => $response->status()]);

            return null;
        }

        return $response->json('response.GeoObjectCollection.featureMember.0.GeoObject');
    }

    private function toPoint(array $geoObject): ?array
    {
        $pos = $geoObject['Point']['pos'] ?? null;

        if (! $pos) {
            return null;
        }

        // Yandex returns the position as "longitude latitude".
        [$lng, $lat] = array_map('floatval', explode(' ', trim($pos)));

        return [
            'lat' => $lat,
            'lng' => $lng,
            'name' => $geoObject['name'] ?? '',
            'address' => $geoObject['metaDataProperty']['GeocoderMetaData']['text']
                ?? $geoObject['name']
                ?? '',
        ];
    }
}
