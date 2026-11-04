<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

// Поиск организаций по названию в подсказках адресов: бесплатный
// OpenStreetMap Nominatim вместо платного Геопоиска Яндекса.
// Организации идут первыми в общем списке точек геокодера, адресные
// результаты Яндекса при этом не должны страдать ни от падения
// Nominatim, ни от отключения флага.
class GeocodeOrganizationsTest extends TestCase
{
    use RefreshDatabase;

    private User $client;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.yandex.geocoder_key' => 'test-geocoder-key']);

        $this->client = User::factory()->create(['role' => 'client']);
    }

    private function authHeaders(): array
    {
        return ['Authorization' => 'Bearer '.auth('api')->login($this->client)];
    }

    private function yandexResponse(): array
    {
        return [
            'response' => [
                'GeoObjectCollection' => [
                    'featureMember' => [[
                        'GeoObject' => [
                            'name' => 'улица Советская, 3',
                            'metaDataProperty' => ['GeocoderMetaData' => [
                                'text' => 'Беларусь, Могилёвская область, Быхов, улица Советская, 3',
                                'Address' => ['Components' => [
                                    ['kind' => 'country', 'name' => 'Беларусь'],
                                    ['kind' => 'province', 'name' => 'Могилёвская область'],
                                    ['kind' => 'locality', 'name' => 'Быхов'],
                                    ['kind' => 'street', 'name' => 'улица Советская'],
                                    ['kind' => 'house', 'name' => '3'],
                                ]],
                            ]],
                            'Point' => ['pos' => '30.240000 53.521000'],
                        ],
                    ]],
                ],
            ],
        ];
    }

    private function fakeBoth(mixed $nominatim): void
    {
        Http::fake([
            'geocode-maps.yandex.ru/*' => Http::response($this->yandexResponse(), 200),
            'nominatim.openstreetmap.org/*' => $nominatim instanceof \Closure
                ? $nominatim
                : Http::response($nominatim, 200),
        ]);
    }

    private function danaMall(): array
    {
        return [[
            'lat' => '53.931100',
            'lon' => '27.678900',
            'name' => 'Дана Молл',
            'display_name' => 'Дана Молл, улица Петра Мстиславца, 11, Минск, Беларусь',
            'class' => 'amenity',
            'type' => 'mall',
            'address' => [
                'road' => 'улица Петра Мстиславца',
                'house_number' => '11',
                'city' => 'Минск',
            ],
        ]];
    }

    public function test_organization_point_goes_first_with_name_kind_and_source(): void
    {
        $this->fakeBoth($this->danaMall());

        $response = $this->getJson(
            '/api/v1/geocode?address='.urlencode('Дана Молл').'&limit=5',
            $this->authHeaders(),
        );

        $response->assertOk()
            ->assertJsonPath('data.0.kind', 'org')
            ->assertJsonPath('data.0.source', 'osm')
            ->assertJsonPath('data.0.name', 'Дана Молл')
            ->assertJsonPath('data.0.display_address', 'Минск, улица Петра Мстиславца, 11')
            ->assertJsonPath('data.0.locality', 'Минск')
            ->assertJsonPath('data.0.lat', 53.9311)
            ->assertJsonPath('data.0.lng', 27.6789)
            // Адресная точка Яндекса остаётся в списке следом.
            ->assertJsonPath('data.1.display_address', 'Быхов, улица Советская, 3');

        $this->assertArrayNotHasKey('kind', $response->json('data.1'));
    }

    public function test_nominatim_failure_still_returns_yandex_results(): void
    {
        $this->fakeBoth(fn () => Http::response('Service Unavailable', 503));

        $response = $this->getJson(
            '/api/v1/geocode?address='.urlencode('Быхов, Советская').'&limit=5',
            $this->authHeaders(),
        );

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.display_address', 'Быхов, улица Советская, 3');

        $this->assertArrayNotHasKey('kind', $response->json('data.0'));
    }

    public function test_disabled_flag_returns_yandex_only_and_skips_nominatim(): void
    {
        config(['services.osm.enabled' => false]);

        $this->fakeBoth($this->danaMall());

        $this->getJson(
            '/api/v1/geocode?address='.urlencode('Дана Молл').'&limit=5',
            $this->authHeaders(),
        )
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.display_address', 'Быхов, улица Советская, 3');

        Http::assertNotSent(
            fn ($request) => str_contains($request->url(), 'nominatim.openstreetmap.org')
        );
    }
}
