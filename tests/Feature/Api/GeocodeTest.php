<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GeocodeTest extends TestCase
{
    use RefreshDatabase;

    private User $client;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.yandex.geocoder_key' => 'test-geocoder-key']);

        $this->client = User::factory()->create(['role' => 'client']);
    }

    /**
     * Login is deliberately per test: logging in inside setUp leaves
     * the api guard authenticated for the whole test, which would
     * silently break the requires-auth test.
     */
    private function authHeaders(): array
    {
        return ['Authorization' => 'Bearer '.auth('api')->login($this->client)];
    }

    private function fakeYandex(): void
    {
        Http::fake([
            'geocode-maps.yandex.ru/*' => Http::response([
                'response' => [
                    'GeoObjectCollection' => [
                        'featureMember' => [
                            [
                                'GeoObject' => [
                                    'name' => 'улица Ленина, 10',
                                    'metaDataProperty' => [
                                        'GeocoderMetaData' => [
                                            'text' => 'Беларусь, Могилёвская область, Быхов, улица Ленина, 10',
                                        ],
                                    ],
                                    'Point' => ['pos' => '30.237100 53.520300'],
                                ],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);
    }

    public function test_geocode_returns_point_for_address(): void
    {
        $this->fakeYandex();

        $this->getJson('/api/v1/geocode?address='.urlencode('Быхов, улица Ленина, 10'), $this->authHeaders())
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.lat', 53.5203)
            ->assertJsonPath('data.lng', 30.2371);
    }

    public function test_reverse_geocode_returns_address_for_point(): void
    {
        $this->fakeYandex();

        $this->getJson('/api/v1/reverse-geocode?lat=53.5203&lng=30.2371', $this->authHeaders())
            ->assertOk()
            ->assertJsonPath('data.address', 'Беларусь, Могилёвская область, Быхов, улица Ленина, 10');
    }

    public function test_geocode_requires_auth(): void
    {
        $this->getJson('/api/v1/geocode?address=Быхов')
            ->assertUnauthorized();
    }

    public function test_geocode_returns_503_when_key_is_missing(): void
    {
        config(['services.yandex.geocoder_key' => null]);
        Http::fake();

        $this->getJson('/api/v1/geocode?address=Быхов', $this->authHeaders())
            ->assertStatus(503);
    }

    public function test_geocode_validates_input(): void
    {
        $this->getJson('/api/v1/geocode', $this->authHeaders())
            ->assertUnprocessable()
            ->assertJsonValidationErrors('address');
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

    public function test_geocode_with_limit_returns_list_of_points(): void
    {
        $this->fakeYandexMembers([
            $this->geoMember('улица Ленина, 10', 'Беларусь, Могилёвская область, Быхов, улица Ленина, 10', '30.237100 53.520300'),
            $this->geoMember('улица Ленина, 12', 'Беларусь, Могилёвская область, Быхов, улица Ленина, 12', '30.238100 53.521300'),
            $this->geoMember('улица Ленина, 1', 'Беларусь, Могилёвская область, Быхов, улица Ленина, 1', '30.236100 53.519300'),
        ]);

        $response = $this->getJson('/api/v1/geocode?address='.urlencode('Быхов, Ленина').'&limit=5', $this->authHeaders())
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.lat', 53.5203)
            ->assertJsonPath('data.0.lng', 30.2371)
            ->assertJsonPath('data.1.name', 'улица Ленина, 12')
            ->assertJsonPath('data.2.address', 'Беларусь, Могилёвская область, Быхов, улица Ленина, 1');

        // data must be a JSON list (sequential keys), not an object.
        $this->assertSame([0, 1, 2], array_keys($response->json('data')));

        Http::assertSent(fn ($request) => str_contains($request->url(), 'results=5'));
    }

    public function test_geocode_limit_is_capped_at_five_results(): void
    {
        $members = [];

        for ($i = 1; $i <= 7; $i++) {
            $members[] = $this->geoMember("улица Ленина, {$i}", "Беларусь, Быхов, улица Ленина, {$i}", '30.237100 53.520300');
        }

        $this->fakeYandexMembers($members);

        $this->getJson('/api/v1/geocode?address=Ленина&limit=5', $this->authHeaders())
            ->assertOk()
            ->assertJsonCount(5, 'data');
    }

    public function test_geocode_limit_above_five_is_rejected(): void
    {
        $this->getJson('/api/v1/geocode?address=Быхов&limit=9', $this->authHeaders())
            ->assertUnprocessable()
            ->assertJsonValidationErrors('limit');
    }

    public function test_geocode_with_limit_one_keeps_single_object_shape(): void
    {
        $this->fakeYandex();

        $this->getJson('/api/v1/geocode?address=Быхов&limit=1', $this->authHeaders())
            ->assertOk()
            ->assertJsonPath('data.lat', 53.5203)
            ->assertJsonPath('data.address', 'Беларусь, Могилёвская область, Быхов, улица Ленина, 10');
    }

    public function test_geocode_with_limit_returns_empty_list_when_nothing_found(): void
    {
        $this->fakeYandexMembers([]);

        $this->getJson('/api/v1/geocode?address='.urlencode('Несуществующий адрес').'&limit=5', $this->authHeaders())
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data', []);
    }

    public function test_geocode_returns_404_when_nothing_found(): void
    {
        $this->fakeYandexMembers([]);

        $this->getJson('/api/v1/geocode?address='.urlencode('Несуществующий адрес'), $this->authHeaders())
            ->assertNotFound()
            ->assertJsonPath('success', false);
    }

    public function test_reverse_geocode_returns_short_address(): void
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
            ->assertJsonPath('data.address', 'Беларусь, Могилёвская область, Быхов, улица Ленина, 10')
            ->assertJsonPath('data.short_address', 'улица Ленина, 10, Быхов');
    }

    public function test_reverse_short_address_uses_province_when_no_locality(): void
    {
        $this->fakeYandexMembers([
            $this->geoMember('улица Ленина, 10', 'Беларусь, Могилёвская область, улица Ленина, 10', '30.237100 53.520300', [
                ['kind' => 'country', 'name' => 'Беларусь'],
                ['kind' => 'province', 'name' => 'Могилёвская область'],
                ['kind' => 'street', 'name' => 'улица Ленина'],
                ['kind' => 'house', 'name' => '10'],
            ]),
        ]);

        $this->getJson('/api/v1/reverse-geocode?lat=53.5203&lng=30.2371', $this->authHeaders())
            ->assertOk()
            ->assertJsonPath('data.short_address', 'улица Ленина, 10, Могилёвская область');
    }

    public function test_reverse_short_address_is_null_without_components(): void
    {
        // The default fixture carries no Address components at all.
        $this->fakeYandex();

        $this->getJson('/api/v1/reverse-geocode?lat=53.5203&lng=30.2371', $this->authHeaders())
            ->assertOk()
            ->assertJsonStructure(['data' => ['short_address']])
            ->assertJsonPath('data.short_address', null);
    }
}
