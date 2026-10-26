<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YandexGeocodeService
{
    private const API_URL = 'https://geocode-maps.yandex.ru/1.x/';

    /** The client app shows at most this many address suggestions. */
    public const MAX_RESULTS = 5;

    public function isConfigured(): bool
    {
        return ! empty(config('services.yandex.geocoder_key'));
    }

    /**
     * Address text -> point. Returns
     * ['lat' => float, 'lng' => float, 'name' => string, 'address' => string, 'short_address' => ?string]
     * or null when nothing is found or the geocoder is unavailable.
     */
    public function geocode(string $address): ?array
    {
        $geoObject = $this->request($address);

        return $geoObject ? $this->toPoint($geoObject) : null;
    }

    /**
     * Address text -> up to $limit points, best match first. Used by the
     * client destination search while the Yandex Suggest API is
     * unavailable: the geocoder itself can return several candidates.
     * An empty list means "nothing to suggest" - also when the geocoder
     * request fails, so the search UI shows no suggestions instead of
     * an error.
     */
    public function geocodeMany(string $address, int $limit): array
    {
        $limit = max(1, min($limit, self::MAX_RESULTS));

        $geoObjects = $this->fetch($address, $limit);

        if ($geoObjects === null) {
            return [];
        }

        $points = [];

        foreach ($geoObjects as $geoObject) {
            $point = $this->toPoint($geoObject);

            if ($point !== null) {
                $points[] = $point;
            }
        }

        return array_slice($points, 0, $limit);
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
        $geoObjects = $this->fetch($geocode, 1, $extra);

        return $geoObjects[0] ?? null;
    }

    /**
     * Raw GeoObject entries for a query, best match first. Returns null
     * when the geocoder is not configured or the request failed, and an
     * empty list when Yandex answered but found nothing.
     */
    private function fetch(string $geocode, int $results, array $extra = []): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $response = Http::get(self::API_URL, array_merge([
                'apikey' => config('services.yandex.geocoder_key'),
                'geocode' => $geocode,
                'format' => 'json',
                'results' => $results,
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

        $members = $response->json('response.GeoObjectCollection.featureMember') ?? [];

        return array_values(array_filter(array_map(
            fn ($member) => is_array($member) ? ($member['GeoObject'] ?? null) : null,
            $members,
        )));
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
            'short_address' => $this->shortAddress($geoObject),
        ];
    }

    /**
     * Short address for compact UI (the pickup balloon): street and
     * house first, then the city - no country, and the region only
     * when there is no city. Falls back to the best partial address
     * and to null when Yandex returned no address components at all.
     * Example: "улица Ленина, 10, Быхов".
     */
    private function shortAddress(array $geoObject): ?string
    {
        $components = $geoObject['metaDataProperty']['GeocoderMetaData']['Address']['Components'] ?? [];

        $byKind = [];

        foreach ($components as $component) {
            if (is_array($component) && isset($component['kind'], $component['name'])) {
                $byKind[$component['kind']] = $component['name'];
            }
        }

        $parts = [];

        // The geocoder names this component "street"; older
        // AddressDetails payloads call it "thoroughfare".
        $street = $byKind['street'] ?? $byKind['thoroughfare'] ?? null;

        if ($street) {
            $parts[] = $street;
        }

        if (! empty($byKind['house'])) {
            $parts[] = $byKind['house'];
        }

        $city = $byKind['locality'] ?? $byKind['province'] ?? null;

        if ($city) {
            $parts[] = $city;
        }

        return $parts === [] ? null : implode(', ', $parts);
    }
}
