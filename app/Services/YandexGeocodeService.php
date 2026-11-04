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

    /** Free organization search (OpenStreetMap Nominatim). */
    private const NOMINATIM_URL = 'https://nominatim.openstreetmap.org/search';

    /** Nominatim must never slow the address search down. */
    private const NOMINATIM_TIMEOUT_SECONDS = 3;

    /** Nominatim usage policy requires a UA that identifies the app. */
    private const NOMINATIM_USER_AGENT = 'TaxiDeriver geocode service (Laravel backend)';

    /** How many organization candidates Nominatim is asked for. */
    private const ORG_FETCH_LIMIT = 5;

    /** Points closer than this are the same place (dedupe radius). */
    private const ORG_DEDUPE_RADIUS_M = 50;

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

            $points = array_slice(array_merge($houses, $rest), 0, self::MAX_RESULTS_WITH_HOUSES);
        } else {
            $points = array_slice($points, 0, $limit);
        }

        return $this->withOrganizations($address, $points);
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
        $area = $this->serviceArea();

        if ($area === null) {
            return [];
        }

        // Yandex spn is "longitude span, latitude span" in degrees.
        $dLat = $area['radius_km'] / 110.57;
        $dLon = min($area['radius_km'] / (111.32 * cos(deg2rad($area['lat']))), 360.0);

        return [
            'll' => $this->formatDegrees($area['lon']).','.$this->formatDegrees($area['lat']),
            'spn' => $this->formatDegrees($dLon).','.$this->formatDegrees($dLat),
            'rspn' => 1,
        ];
    }

    /**
     * The configured service area as centre + radius, or null when
     * the restriction is switched off (fields cleared by the admin).
     * Shared by the Yandex restriction above and the OpenStreetMap
     * organization search below.
     */
    private function serviceArea(): ?array
    {
        $lat = Setting::get('service_area_lat', self::DEFAULT_AREA_LAT);
        $lon = Setting::get('service_area_lon', self::DEFAULT_AREA_LON);
        $radiusKm = Setting::get('service_area_radius_km', self::DEFAULT_AREA_RADIUS_KM);

        if (! is_numeric($lat) || ! is_numeric($lon) || ! is_numeric($radiusKm) || (float) $radiusKm <= 0) {
            return null;
        }

        return [
            'lat' => (float) $lat,
            'lon' => (float) $lon,
            'radius_km' => (float) $radiusKm,
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

    /**
     * Named places (organizations) from OpenStreetMap, merged ahead
     * of the address results, so a search by name («Дана Молл»)
     * finds the place and not just streets. The Yandex organizations
     * API (Геопоиск) is paid, so free Nominatim is used instead.
     *
     * Attribution: org points carry source='osm' - every interface
     * that displays them MUST keep the OpenStreetMap attribution
     * visible: «© OpenStreetMap contributors».
     *
     * Any Nominatim problem (disabled via OSM_SEARCH_ENABLED,
     * timeout, error, garbage) silently keeps the Yandex-only list:
     * the address search never depends on OSM being up.
     */
    private function withOrganizations(string $address, array $points): array
    {
        $orgs = $this->organizationPoints($address);

        if ($orgs === []) {
            return $points;
        }

        $rest = array_values(array_filter($points, function ($point) use ($orgs) {
            foreach ($orgs as $org) {
                if ($this->distanceMeters($org['lat'], $org['lng'], $point['lat'], $point['lng']) < self::ORG_DEDUPE_RADIUS_M) {
                    return false;
                }
            }

            return true;
        }));

        return array_slice(array_merge($orgs, $rest), 0, self::MAX_RESULTS_WITH_HOUSES);
    }

    private function organizationPoints(string $query): array
    {
        if (! config('services.osm.enabled', true)) {
            return [];
        }

        $params = [
            'q' => $query,
            'format' => 'jsonv2',
            'limit' => self::ORG_FETCH_LIMIT,
            'countrycodes' => 'by',
            'addressdetails' => 1,
        ];

        $area = $this->serviceArea();

        if ($area !== null) {
            // The same service-area restriction as the Yandex search:
            // a viewbox around the centre, bounded so results stay in.
            $dLat = $area['radius_km'] / 110.57;
            $dLon = min($area['radius_km'] / (111.32 * cos(deg2rad($area['lat']))), 360.0);

            $params['viewbox'] = implode(',', [
                $this->formatDegrees($area['lon'] - $dLon),
                $this->formatDegrees($area['lat'] + $dLat),
                $this->formatDegrees($area['lon'] + $dLon),
                $this->formatDegrees($area['lat'] - $dLat),
            ]);
            $params['bounded'] = 1;
        }

        try {
            $response = Http::timeout(self::NOMINATIM_TIMEOUT_SECONDS)
                ->withHeaders(['User-Agent' => self::NOMINATIM_USER_AGENT])
                ->get(self::NOMINATIM_URL, $params);
        } catch (\Throwable $e) {
            Log::warning('OSM Nominatim request failed', ['error' => $e->getMessage()]);

            return [];
        }

        if ($response->failed()) {
            Log::warning('OSM Nominatim returned an error', ['status' => $response->status()]);

            return [];
        }

        $items = $response->json();

        if (! is_array($items)) {
            return [];
        }

        $points = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $point = $this->toOrganizationPoint($item, $area);

            if ($point === null) {
                continue;
            }

            foreach ($points as $existing) {
                if ($this->distanceMeters($existing['lat'], $existing['lng'], $point['lat'], $point['lng']) < self::ORG_DEDUPE_RADIUS_M) {
                    continue 2;
                }
            }

            $points[] = $point;
        }

        return $points;
    }

    /**
     * One Nominatim result -> a suggestion point in the same shape
     * as the Yandex points, extended with kind='org' and
     * source='osm'. Returns null for results that are not named
     * places (a bare address wears its street as the name) and for
     * points outside the service area.
     */
    private function toOrganizationPoint(array $item, ?array $area): ?array
    {
        $name = trim((string) ($item['name'] ?? ''));

        if ($name === '' || ! isset($item['lat'], $item['lon'])
            || ! is_numeric($item['lat']) || ! is_numeric($item['lon'])) {
            return null;
        }

        // Settlements, districts, streets and the like are not
        // organizations even when Nominatim names them.
        if (in_array($item['class'] ?? '', ['place', 'boundary', 'highway', 'natural', 'waterway', 'landuse'], true)) {
            return null;
        }

        $lat = (float) $item['lat'];
        $lng = (float) $item['lon'];

        $address = is_array($item['address'] ?? null) ? $item['address'] : [];

        $road = $address['road'] ?? null;
        $house = $address['house_number'] ?? null;

        $streetAddress = trim(implode(' ', array_filter([$road, $house])));

        if ($streetAddress !== '' && mb_strtolower($name) === mb_strtolower($streetAddress)) {
            return null;
        }

        if ($area !== null
            && $this->distanceMeters($area['lat'], $area['lon'], $lat, $lng) > $area['radius_km'] * 1000) {
            return null;
        }

        $locality = $address['city'] ?? $address['town'] ?? $address['village'] ?? $address['state'] ?? null;

        $displayParts = array_filter([$locality, $road, $house]);
        $shortParts = array_filter([$road, $house, $locality]);

        return [
            'lat' => $lat,
            'lng' => $lng,
            'name' => $name,
            'address' => (string) ($item['display_name'] ?? $name),
            'short_address' => $shortParts === [] ? null : implode(', ', $shortParts),
            'display_address' => $displayParts === [] ? (string) ($item['display_name'] ?? $name) : implode(', ', $displayParts),
            'locality' => $locality,
            'kind' => 'org',
            'source' => 'osm',
        ];
    }

    private function distanceMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return 2 * 6371000.0 * asin(min(1.0, sqrt($a)));
    }
}
