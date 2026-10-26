<?php

namespace Tests\Feature\Api;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NewOrderUserTest extends TestCase
{
    use RefreshDatabase;

    private function authHeaders(User $user): array
    {
        $token = auth('api')->login($user);

        return ['Authorization' => 'Bearer ' . $token];
    }

    public function test_creates_order_from_client_coordinates_without_geocoding(): void
    {
        Http::fake();
        Event::fake();
        $user = User::factory()->create();

        $response = $this->postJson('/api/orders/newOrderUser', [
            'fromAddress' => 'Быхов, ул. Ленина, 1',
            'fromLat' => 53.5203,
            'fromLon' => 30.2371,
            'toAddress' => 'Быхов, ул. Советская, 5',
            'toLat' => 53.5250,
            'toLon' => 30.2450,
            'distance' => 1400,
            'duration' => 360,
        ], $this->authHeaders($user));

        $response->assertOk()->assertJson(['status' => true]);
        Http::assertNothingSent();

        $order = Order::first();
        $this->assertNotNull($order);
        $this->assertEqualsWithDelta(53.5203, (float) $order->from_lat, 0.0001);
        $this->assertEqualsWithDelta(30.2371, (float) $order->from_lng, 0.0001);
        $this->assertEqualsWithDelta(53.5250, (float) $order->to_lat, 0.0001);
        $this->assertEqualsWithDelta(30.2450, (float) $order->to_lng, 0.0001);
        $this->assertSame(1400, (int) $order->distance);
        $this->assertSame(360, (int) $order->duration);
        $this->assertSame($user->id, $order->client_id);
    }

    public function test_geocodes_addresses_when_coordinates_are_missing(): void
    {
        Config::set('services.yandex.geocoder_key', 'test-key');
        Http::fake([
            '*geocode-maps.yandex.ru*' => Http::response([
                'response' => ['GeoObjectCollection' => ['featureMember' => [
                    ['GeoObject' => ['Point' => ['pos' => '30.2450 53.5250']]],
                ]]],
            ]),
        ]);
        Event::fake();
        $user = User::factory()->create();

        $response = $this->postJson('/api/orders/newOrderUser', [
            'fromAddress' => 'Быхов, ул. Ленина, 1',
            'toAddress' => 'Быхов, ул. Советская, 5',
        ], $this->authHeaders($user));

        $response->assertOk()->assertJson(['status' => true]);

        $order = Order::first();
        $this->assertNotNull($order);
        // Yandex "pos" is "lng lat": the stored latitude must be 53.5250.
        $this->assertEqualsWithDelta(53.5250, (float) $order->to_lat, 0.0001);
        $this->assertEqualsWithDelta(30.2450, (float) $order->to_lng, 0.0001);
        $this->assertEqualsWithDelta(53.5250, (float) $order->from_lat, 0.0001);
    }

    public function test_app_order_source_is_forced_to_app(): void
    {
        Http::fake();
        Event::fake();
        $user = User::factory()->create();

        $response = $this->postJson('/api/orders/newOrderUser', [
            'fromAddress' => 'Быхов, ул. Ленина, 1',
            'fromLat' => 53.5203,
            'fromLon' => 30.2371,
            'toAddress' => 'Быхов, ул. Советская, 5',
            'toLat' => 53.5250,
            'toLon' => 30.2450,
            // Приложение не может подменить источник заказа
            'source' => 'phone',
        ], $this->authHeaders($user));

        $response->assertOk()->assertJson(['status' => true]);
        $this->assertSame('app', Order::first()->source);
    }

    public function test_requires_to_address(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/orders/newOrderUser', [
            'fromAddress' => 'Быхов, ул. Ленина, 1',
        ], $this->authHeaders($user))->assertStatus(400);

        $this->assertSame(0, Order::count());
    }

    public function test_requires_authentication(): void
    {
        $this->postJson('/api/orders/newOrderUser', [
            'toAddress' => 'Быхов, ул. Советская, 5',
        ])->assertUnauthorized();
    }
}
