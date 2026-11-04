<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GeocodeHouseSuggestionsTest extends TestCase
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

    private function geoMember(string $kind, string $name, string $text, string $pos, array $components): array
    {
        return [
            'GeoObject' => [
                'name' => $name,
                'metaDataProperty' => [
                    'GeocoderMetaData' => [
                        'kind' => $kind,
                        'text' => $text,
                        'Address' => ['Components' => $components],
                    ],
                ],
                'Point' => ['pos' => $pos],
            ],
        ];
    }

    private function furmanovaComponents(?string $house = null): array
    {
        $components = [
            ['kind' => 'country', 'name' => 'Беларусь'],
            ['kind' => 'province', 'name' => 'Минская область'],
            ['kind' => 'locality', 'name' => 'Дзержинск'],
            ['kind' => 'street', 'name' => 'улица Фурманова'],
        ];

        if ($house !== null) {
            $components[] = ['kind' => 'house', 'name' => $house];
        }

        return $components;
    }

    /** What Yandex answers to the bare query «Фурманова»: streets only. */
    private function streetMembers(): array
    {
        return [
            $this->geoMember('street', 'улица Фурманова', 'Беларусь, Минская область, Дзержинск, улица Фурманова', '28.685000 53.688000', $this->furmanovaComponents()),
            $this->geoMember('street', 'улица Фурманова', 'Беларусь, Минск, улица Фурманова', '27.560000 53.900000', [
                ['kind' => 'country', 'name' => 'Беларусь'],
                ['kind' => 'locality', 'name' => 'Минск'],
                ['kind' => 'street', 'name' => 'улица Фурманова'],
            ]),
        ];
    }

    /** What Yandex answers to the follow-up kind=house request. */
    private function houseMembers(): array
    {
        return [
            $this->geoMember('house', 'улица Фурманова, 2', 'Беларусь, Минская область, Дзержинск, улица Фурманова, 2', '28.686000 53.688500', $this->furmanovaComponents('2')),
            $this->geoMember('house', 'улица Фурманова, 3', 'Беларусь, Минская область, Дзержинск, улица Фурманова, 3', '28.687000 53.689000', $this->furmanovaComponents('3')),
        ];
    }

    /**
     * Fake Yandex: the follow-up house request is the one carrying
     * kind=house; everything else gets $streetMembers.
     */
    private function fakeYandex(callable $houseResponder): void
    {
        Http::fake(function ($request) use ($houseResponder) {
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

            if (($query['kind'] ?? null) === 'house') {
                return $houseResponder($query);
            }

            return $this->yandexResponse($this->streetMembers());
        });
    }

    private function yandexResponse(array $members, int $status = 200)
    {
        return Http::response([
            'response' => ['GeoObjectCollection' => ['featureMember' => $members]],
        ], $status);
    }

    public function test_street_query_without_house_number_adds_houses_of_the_top_street(): void
    {
        $this->fakeYandex(fn (array $query) => $this->yandexResponse($this->houseMembers()));

        $this->getJson('/api/v1/geocode?address='.urlencode('Фурманова').'&limit=5', $this->authHeaders())
            ->assertOk()
            ->assertJsonCount(4, 'data')
            // Houses of the top (nearest) street lead the list.
            ->assertJsonPath('data.0.display_address', 'Дзержинск, улица Фурманова, 2')
            ->assertJsonPath('data.0.locality', 'Дзержинск')
            ->assertJsonPath('data.1.display_address', 'Дзержинск, улица Фурманова, 3')
            // The original street results stay, after the houses.
            ->assertJsonPath('data.2.display_address', 'Дзержинск, улица Фурманова')
            ->assertJsonPath('data.3.display_address', 'Минск, улица Фурманова');

        // The follow-up request geocodes «locality street» with
        // kind=house inside the same service area.
        Http::assertSent(function ($request) {
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

            return ($query['kind'] ?? null) === 'house'
                && str_contains($query['geocode'] ?? '', 'Дзержинск')
                && str_contains($query['geocode'] ?? '', 'улица Фурманова')
                && (string) ($query['rspn'] ?? '') === '1';
        });
    }

    public function test_query_with_a_digit_does_not_trigger_the_house_request(): void
    {
        Http::fake([
            'geocode-maps.yandex.ru/*' => Http::response([
                'response' => [
                    'GeoObjectCollection' => ['featureMember' => [
                        $this->geoMember('house', 'улица Фурманова, 2', 'Беларусь, Минская область, Дзержинск, улица Фурманова, 2', '28.686000 53.688500', $this->furmanovaComponents('2')),
                    ]],
                ],
            ], 200),
        ]);

        $this->getJson('/api/v1/geocode?address='.urlencode('Фурманова 2').'&limit=5', $this->authHeaders())
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.display_address', 'Дзержинск, улица Фурманова, 2');

        Http::assertSentCount(1);
    }

    public function test_empty_house_answer_keeps_the_original_results(): void
    {
        $this->fakeYandex(fn (array $query) => $this->yandexResponse([]));

        $this->getJson('/api/v1/geocode?address='.urlencode('Фурманова').'&limit=5', $this->authHeaders())
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.display_address', 'Дзержинск, улица Фурманова')
            ->assertJsonPath('data.1.display_address', 'Минск, улица Фурманова');

        // The follow-up request did happen - it just found nothing.
        Http::assertSentCount(2);
    }

    public function test_failed_house_request_keeps_the_original_results(): void
    {
        $this->fakeYandex(fn (array $query) => $this->yandexResponse([], 500));

        $this->getJson('/api/v1/geocode?address='.urlencode('Фурманова').'&limit=5', $this->authHeaders())
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.display_address', 'Дзержинск, улица Фурманова')
            ->assertJsonPath('data.1.display_address', 'Минск, улица Фурманова');
    }
}
