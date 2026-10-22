<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GeocodeTest extends TestCase
{
    use RefreshDatabase;

    private array $headers;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.yandex.geocoder_key' => 'test-geocoder-key']);

        $client = User::factory()->create(['role' => 'client']);
        $this->headers = ['Authorization' => 'Bearer '.auth('api')->login($client)];
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

        $this->getJson('/api/v1/geocode?address='.urlencode('Быхов, улица Ленина, 10'), $this->headers)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.lat', 53.5203)
            ->assertJsonPath('data.lng', 30.2371);
    }

    public function test_reverse_geocode_returns_address_for_point(): void
    {
        $this->fakeYandex();

        $this->getJson('/api/v1/reverse-geocode?lat=53.5203&lng=30.2371', $this->headers)
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

        $this->getJson('/api/v1/geocode?address=Быхов', $this->headers)
            ->assertStatus(503);
    }

    public function test_geocode_validates_input(): void
    {
        $this->getJson('/api/v1/geocode', $this->headers)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('address');
    }
}
