<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YandexGeocodeService
{
    private const API_URL = 'https://geocode-maps.yandex.ru/1.x/';

    /** The client app shows at most this many address suggestions. */
    public const MAX_RESULTS = 5;

    /**
     * When house suggestions are merged in for a bare street query,
     * the list may grow to this many entries.
     */
    public const MAX_RESULTS_WITH_HOUSES = 8;

    /** How many houses of the top street lead the merged list. */
    private const MAX_HOUSES = 5;

    /** How many houses the follow-up kind=house request asks for. */
    private const HOUSE_FETCH_RESULTS = 6;

    /** Service-area defaults: the whole of Belarus. */
    private const DEFAULT_AREA_LAT = '53.7';

    private const DEFAULT_AREA_LON = '28.0';

    private const DEFAULT_AREA_RADIUS_KM = '350';

    public function isConfigured(): bool
    {
        return ! empty(config('services.yandex.geocoder_key'));
    }

    /**
     * Address text -> point. Returns
     * ['lat' => float, 'lng' => float, 'name' => string, 'address' => string,
     *  'short_address' => ?string, 'display_address' => string, 'locality' => ?string]
     * or null when nothing is found or the geocoder is unavailable.
     */
    public function geocode(string $address): ?array
    {
        $geoObject = $this->request($address, $this->serviceAreaParams());

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

        $areaParams = $this->serviceAreaParams();

        $geoObjects = $this->fetch($address, $limit, $areaParams);

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

        $houses = $this->housePoints($address, $geoObjects, $areaParams);

        if ($houses !== []) {
            $seen = [];

            foreach ($houses as $house) {
                $seen[$house['lat'].','.$house['lng']] = true;
            }

            $rest = array_values(array_filter(
                $points,
                fn ($point) => ! isset($seen[$point['lat'].','.$point['lng']]),
            ));

            return array_slice(array_merge($houses, $rest), 0, self::MAX_RESULTS_WITH_HOUSES);
        }

        return array_slice($points, 0, $limit);
    }

    /**
     * House-level suggestions for a bare street query («Фурманова»):
     * the geocoder answers such a query with the streets themselves,
     * so the houses of the top street result are fetched with a
     * follow-up kind=house request in the same service area. Any
     * failure or empty answer returns an empty list and the caller
     * keeps the original results unchanged.
     */
    private function housePoints(string $address, array $geoObjects, array $areaParams): array
    {
        // A digit in the query means the user is already narrowing
        // the address down (a house number) - no expansion needed.
        if (preg_match('/\\d/u', $address)) {
            return [];
        }

        $streetObject = null;

        foreach ($geoObjects as $geoObject) {
            if (($geoObject['metaDataProperty']['GeocoderMetaData']['kind'] ?? null) === 'street') {
                $streetObject = $geoObject;
                break;
            }
        }

        if ($streetObject === null) {
            return [];
        }

        $byKind = $this->addressComponents($streetObject);

        $streetName = $byKind['street'] ?? $byKind['thoroughfare'] ?? null;

        if (! $streetName) {
            return [];
        }

        $query = trim(($this->localityName($byKind) ?? '').' '.$streetName);

        $houseObjects = $this->fetch(
            $query,
            self::HOUSE_FETCH_RESULTS,
            array_merge($areaParams, ['kind' => 'house']),
        );

        if ($houseObjects === null) {
            return [];
        }

        $points = [];

        foreach ($houseObjects as $houseObject) {
            if (($houseObject['metaDataProperty']['GeocoderMetaData']['kind'] ?? null) !== 'house') {
                continue;
            }

            $point = $this->toPoint($houseObject);

            if ($point !== null) {
                $points[] = $point;
            }

            if (count($points) >= self::MAX_HOUSES) {
                break;
            }
        }

        return $points;
    }

    /**
     * Point -> nearest house address. Deliberately NOT restricted to
     * the service area: the pickup point is a fact, not a search.
     */
    public function reverse(float $lat, float $lng): ?array
    {
        $geoObject = $this->request($lng.','.$lat, ['kind' => 'house']);

        return $geoObject ? $this->toPoint($geoObject) : null;
    }

    /**
     * Strict service-area restriction for the address search, from the
     * admin settings (service_area_lat / service_area_lon /
     * service_area_radius_km). While the settings were never saved the
     * defaults above apply; an admin can clear the fields to switch
     * the restriction off entirely (empty params, old behaviour).
     */
    private function serviceAreaParams(): array
    {
        $lat = Setting::get('service_area_lat', self::DEFAULT_AREA_LAT);
        $lon = Setting::get('service_area_lon', self::DEFAULT_AREA_LON);
        $radiusKm = Setting::get('service_area_radius_km', self::DEFAULT_AREA_RADIUS_KM);

        if (! is_numeric($lat) || ! is_numeric($lon) || ! is_numeric($radiusKm) || (float) $radiusKm <= 0) {
            return [];
        }

        $lat = (float) $lat;
        $lon = (float) $lon;
        $radiusKm = (float) $radiusKm;

        // Yandex spn is "longitude span, latitude span" in degrees.
        $dLat = $radiusKm / 110.57;
        $dLon = min($radiusKm / (111.32 * cos(deg2rad($lat))), 360.0);

        return [
            'll' => $this->formatDegrees($lon).','.$this->formatDegrees($lat),
            'spn' => $this->formatDegrees($dLon).','.$this->formatDegrees($dLat),
            'rspn' => 1,
        ];
    }

    private function formatDegrees(float $value): string
    {
        return rtrim(rtrim(sprintf('%.6F', $value), '0'), '.');
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

        $byKind = $this->addressComponents($geoObject);

        return [
            'lat' => $lat,
            'lng' => $lng,
            'name' => $geoObject['name'] ?? '',
            'address' => $geoObject['metaDataProperty']['GeocoderMetaData']['text']
                ?? $geoObject['name']
                ?? '',
            'short_address' => $this->shortAddress($byKind),
            'display_address' => $this->displayAddress($geoObject, $byKind),
            'locality' => $this->localityName($byKind),
        ];
    }

    /**
     * Address components of a GeoObject as a kind => name map.
     */
    private function addressComponents(array $geoObject): array
    {
        $components = $geoObject['metaDataProperty']['GeocoderMetaData']['Address']['Components'] ?? [];

        $byKind = [];

        foreach ($components as $component) {
            if (is_array($component) && isset($component['kind'], $component['name'])) {
                $byKind[$component['kind']] = $component['name'];
            }
        }

        return $byKind;
    }

    /**
     * Settlement name for the suggestion subtitle: the locality, with
     * the district / area as fallback for places Yandex does not mark
     * as a locality. Null when there is nothing settlement-like at all.
     */
    private function localityName(array $byKind): ?string
    {
        return $byKind['locality'] ?? $byKind['district'] ?? $byKind['area'] ?? null;
    }

    /**
     * Address line for the client UI: settlement, street, house - no
     * country and no region. Falls back to the formatted address with
     * the country / region prefix stripped when Yandex returned no
     * usable components.
     * Example: "Быхов, улица Ленина, 10".
     */
    private function displayAddress(array $geoObject, array $byKind): string
    {
        $parts = [];

        $place = $this->localityName($byKind);

        if ($place) {
            $parts[] = $place;
        }

        // The geocoder names this component "street"; older
        // AddressDetails payloads call it "thoroughfare".
        $street = $byKind['street'] ?? $byKind['thoroughfare'] ?? null;

        if ($street) {
            $parts[] = $street;
        }

        if (! empty($byKind['house'])) {
            $parts[] = $byKind['house'];
        }

        if ($parts !== []) {
            return implode(', ', $parts);
        }

        $text = $geoObject['metaDataProperty']['GeocoderMetaData']['text']
            ?? $geoObject['name']
            ?? '';

        $segments = array_values(array_filter(
            array_map('trim', explode(',', (string) $text)),
            fn ($segment) => $segment !== '',
        ));

        $prefix = array_filter([$byKind['country'] ?? null, $byKind['province'] ?? null]);

        if ($prefix !== []) {
            while ($segments !== [] && in_array($segments[0], $prefix, true)) {
                array_shift($segments);
            }
        } elseif (count($segments) > 2) {
            // Yandex formatted addresses open with country, region.
            $segments = array_slice($segments, 2);
        }

        return implode(', ', $segments);
    }

    /**
     * Short address for compact UI (the pickup balloon): street and
     * house first, then the city - no country, and the region only
     * when there is no city. Falls back to the best partial address
     * and to null when Yandex returned no address components at all.
     * Example: "улица Ленина, 10, Быхов".
     */
    private function shortAddress(array $byKind): ?string
    {
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
