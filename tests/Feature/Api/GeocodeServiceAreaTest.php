<?php

namespace Tests\Feature\Api;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GeocodeServiceAreaTest extends TestCase
{
    use RefreshDatabase;

    private User $client;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.yandex.geocoder_key' => 'test-geocoder-key']);
        config(['services.osm.enabled' => false]);

        $this->client = User::factory()->create(['role' => 'client']);
    }

    private function authHeaders(): array
    {
        return ['Authorization' => 'Bearer '.auth('api')->login($this->client)];
    }

    private function geoMember(string $name, string $text, string $pos, ?array $components = null): array
    {
        $metaData = ['text' => $text];

        if ($components !== null) {
            $metaData['Address'] = ['Components' => $components];
        }

        return [
            'GeoObject' => [
                'name' => $name,
                'metaDataProperty' => ['GeocoderMetaData' => $metaData],
                'Point' => ['pos' => $pos],
            ],
        ];
    }

    private function fakeYandexMembers(array $members): void
    {
        Http::fake([
            'geocode-maps.yandex.ru/*' => Http::response([
                'response' => [
                    'GeoObjectCollection' => ['featureMember' => $members],
                ],
            ], 200),
        ]);
    }

    private function sentQuery(callable $check): void
    {
        Http::assertSent(function ($request) use ($check) {
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

            return $check($query);
        });
    }

    public function test_search_request_is_restricted_to_configured_service_area(): void
    {
        Setting::set('service_area_lat', '53.9');
        Setting::set('service_area_lon', '27.5667');
        Setting::set('service_area_radius_km', '30');

        $this->fakeYandexMembers([
            $this->geoMember('улица Ленина, 5', 'Беларусь, Минск, улица Ленина, 5', '27.566700 53.900000'),
        ]);

        $this->getJson('/api/v1/geocode?address='.urlencode('Минск, Ленина').'&limit=5', $this->authHeaders())
            ->assertOk();

        $this->sentQuery(function (array $query) {
            $this->assertSame('27.5667,53.9', $query['ll'] ?? null);
            $this->assertSame('1', (string) ($query['rspn'] ?? ''));

            [$dLon, $dLat] = array_map('floatval', explode(',', $query['spn'] ?? ','));

            // spn is derived from the radius: degrees of latitude and
            // longitude the radius spans around the centre.
            $this->assertEqualsWithDelta(30 / 110.57, $dLat, 0.001);
            $this->assertEqualsWithDelta(30 / (111.32 * cos(deg2rad(53.9))), $dLon, 0.001);

            return true;
        });
    }

    public function test_search_request_uses_belarus_defaults_when_settings_never_saved(): void
    {
        $this->fakeYandexMembers([
            $this->geoMember('улица Ленина, 5', 'Беларусь, Быхов, улица Ленина, 5', '30.237100 53.520300'),
        ]);

        $this->getJson('/api/v1/geocode?address='.urlencode('Быхов, Ленина').'&limit=5', $this->authHeaders())
            ->assertOk();

        $this->sentQuery(function (array $query) {
            $this->assertSame('28,53.7', $query['ll'] ?? null);
            $this->assertSame('1', (string) ($query['rspn'] ?? ''));

            [$dLon, $dLat] = array_map('floatval', explode(',', $query['spn'] ?? ','));

            $this->assertEqualsWithDelta(350 / 110.57, $dLat, 0.001);
            $this->assertEqualsWithDelta(350 / (111.32 * cos(deg2rad(53.7))), $dLon, 0.001);

            return true;
        });
    }

    public function test_search_request_has_no_restriction_when_settings_cleared(): void
    {
        Setting::set('service_area_lat', '');
        Setting::set('service_area_lon', '');
        Setting::set('service_area_radius_km', '');

        $this->fakeYandexMembers([
            $this->geoMember('улица Ленина, 5', 'Беларусь, Быхов, улица Ленина, 5', '30.237100 53.520300'),
        ]);

        $this->getJson('/api/v1/geocode?address='.urlencode('Быхов, Ленина').'&limit=5', $this->authHeaders())
            ->assertOk();

        $this->sentQuery(fn (array $query) => ! isset($query['ll']) && ! isset($query['spn']) && ! isset($query['rspn']));
    }

    public function test_reverse_request_is_not_restricted_to_service_area(): void
    {
        Setting::set('service_area_lat', '53.9');
        Setting::set('service_area_lon', '27.5667');
        Setting::set('service_area_radius_km', '30');

        $this->fakeYandexMembers([
            $this->geoMember('улица Ленина, 5', 'Беларусь, Минск, улица Ленина, 5', '27.566700 53.900000'),
        ]);

        $this->getJson('/api/v1/reverse-geocode?lat=53.9&lng=27.5667', $this->authHeaders())
            ->assertOk();

        $this->sentQuery(fn (array $query) => ! isset($query['ll']) && ! isset($query['spn']) && ! isset($query['rspn']));
    }

    public function test_search_response_includes_display_address_and_locality(): void
    {
        $this->fakeYandexMembers([
            $this->geoMember('улица Ленина, 5', 'Беларусь, Минская область, Дзержинск, улица Ленина, 5', '28.690000 53.684000', [
                ['kind' => 'country', 'name' => 'Беларусь'],
                ['kind' => 'province', 'name' => 'Минская область'],
                ['kind' => 'locality', 'name' => 'Дзержинск'],
                ['kind' => 'street', 'name' => 'улица Ленина'],
                ['kind' => 'house', 'name' => '5'],
            ]),
        ]);

        $this->getJson('/api/v1/geocode?address='.urlencode('Дзержинск, Ленина').'&limit=5', $this->authHeaders())
            ->assertOk()
            ->assertJsonPath('data.0.display_address', 'Дзержинск, улица Ленина, 5')
            ->assertJsonPath('data.0.locality', 'Дзержинск')
            // The pre-existing fields stay untouched.
            ->assertJsonPath('data.0.address', 'Беларусь, Минская область, Дзержинск, улица Ленина, 5')
            ->assertJsonPath('data.0.name', 'улица Ленина, 5');
    }

    public function test_display_address_falls_back_to_district_without_locality(): void
    {
        $this->fakeYandexMembers([
            $this->geoMember('улица Ленина, 5', 'Беларусь, Минская область, Дзержинский район, улица Ленина, 5', '28.690000 53.684000', [
                ['kind' => 'country', 'name' => 'Беларусь'],
                ['kind' => 'province', 'name' => 'Минская область'],
                ['kind' => 'district', 'name' => 'Дзержинский район'],
                ['kind' => 'street', 'name' => 'улица Ленина'],
                ['kind' => 'house', 'name' => '5'],
            ]),
        ]);

        $this->getJson('/api/v1/geocode?address='.urlencode('Ленина').'&limit=5', $this->authHeaders())
            ->assertOk()
            ->assertJsonPath('data.0.display_address', 'Дзержинский район, улица Ленина, 5')
            ->assertJsonPath('data.0.locality', 'Дзержинский район');
    }

    public function test_display_address_without_components_strips_country_and_region_prefix(): void
    {
        $this->fakeYandexMembers([
            $this->geoMember('улица Ленина, 10', 'Беларусь, Могилёвская область, Быхов, улица Ленина, 10', '30.237100 53.520300'),
        ]);

        $this->getJson('/api/v1/geocode?address='.urlencode('Быхов, Ленина').'&limit=5', $this->authHeaders())
            ->assertOk()
            ->assertJsonPath('data.0.display_address', 'Быхов, улица Ленина, 10')
            ->assertJsonPath('data.0.locality', null);
    }

    public function test_reverse_response_includes_display_address_and_locality(): void
    {
        $this->fakeYandexMembers([
            $this->geoMember('улица Ленина, 10', 'Беларусь, Могилёвская область, Быхов, улица Ленина, 10', '30.237100 53.520300', [
                ['kind' => 'country', 'name' => 'Беларусь'],
                ['kind' => 'province', 'name' => 'Могилёвская область'],
                ['kind' => 'locality', 'name' => 'Быхов'],
                ['kind' => 'street', 'name' => 'улица Ленина'],
                ['kind' => 'house', 'name' => '10'],
            ]),
        ]);

        $this->getJson('/api/v1/reverse-geocode?lat=53.5203&lng=30.2371', $this->authHeaders())
            ->assertOk()
            ->assertJsonPath('data.display_address', 'Быхов, улица Ленина, 10')
            ->assertJsonPath('data.locality', 'Быхов')
            ->assertJsonPath('data.short_address', 'улица Ленина, 10, Быхов');
    }
}
