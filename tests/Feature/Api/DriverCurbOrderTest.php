<?php

namespace Tests\Feature\Api;

use App\Models\Car;
use App\Models\DriverLocation;
use App\Models\Order;
use App\Models\User;
use App\Queries\AdminOrdersQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

// Заказ «от борта» (POST /api/v1/driver/orders): водитель с
// привязанным авто сам заводит заказ при посадке пассажира, заказ
// сразу назначен на него, в админке виден с источником «От борта».
class DriverCurbOrderTest extends TestCase
{
    use RefreshDatabase;

    private function authHeaders(User $user): array
    {
        $token = auth('api')->login($user);

        return ['Authorization' => 'Bearer ' . $token];
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'to_lat' => 53.512345,
            'to_lng' => 28.231234,
            'to_address' => 'г. Быхов, ул. Ленина, 10',
            'from_lat' => 53.511111,
            'from_lng' => 28.230001,
        ], $overrides);
    }

    public function test_driver_with_bound_car_creates_curb_order(): void
    {
        $car = Car::factory()->create();
        $driver = User::factory()->driver()->create(['current_car_id' => $car->id]);

        $response = $this->postJson('/api/v1/driver/orders', $this->payload([
            'phone' => '+375 29 111-22-33',
            'distance' => 4200,
            'duration' => 600,
        ]), $this->authHeaders($driver));

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('order.status', Order::STATUS_ASSIGNED)
            ->assertJsonPath('order.source', Order::SOURCE_CURB)
            ->assertJsonPath('order.driver_id', $driver->id)
            ->assertJsonPath('order.car_id', $car->id);

        $order = Order::first();
        $this->assertNotNull($order);
        $this->assertNull($order->client_id);
        $this->assertSame('+375 29 111-22-33', $order->client_phone);
        $this->assertSame('От борта', $order->source_label);
        $this->assertEquals(4200, $order->distance);
        $this->assertEquals(600, $order->duration);
        $this->assertEqualsWithDelta(53.511111, (float) $order->from_lat, 0.000001);
        $this->assertEqualsWithDelta(28.231234, (float) $order->to_lng, 0.000001);
    }

    public function test_pickup_point_falls_back_to_latest_driver_location(): void
    {
        $car = Car::factory()->create();
        $driver = User::factory()->driver()->create(['current_car_id' => $car->id]);
        DriverLocation::create([
            'user_id' => $driver->id,
            'lat' => 53.600001,
            'lng' => 28.300002,
        ]);

        $payload = $this->payload();
        unset($payload['from_lat'], $payload['from_lng']);

        $response = $this->postJson('/api/v1/driver/orders', $payload, $this->authHeaders($driver));

        $response->assertCreated();

        $order = Order::first();
        $this->assertEqualsWithDelta(53.600001, (float) $order->from_lat, 0.000001);
        $this->assertEqualsWithDelta(28.300002, (float) $order->from_lng, 0.000001);
    }

    public function test_driver_without_bound_car_gets_422(): void
    {
        $driver = User::factory()->driver()->create();

        $response = $this->postJson('/api/v1/driver/orders', $this->payload(), $this->authHeaders($driver));

        $response->assertUnprocessable()
            ->assertJsonPath('success', false);

        $this->assertSame(0, Order::count());
    }

    public function test_driver_without_any_pickup_coordinates_gets_422(): void
    {
        $car = Car::factory()->create();
        $driver = User::factory()->driver()->create(['current_car_id' => $car->id]);

        $payload = $this->payload();
        unset($payload['from_lat'], $payload['from_lng']);

        $response = $this->postJson('/api/v1/driver/orders', $payload, $this->authHeaders($driver));

        $response->assertUnprocessable()
            ->assertJsonPath('success', false);

        $this->assertSame(0, Order::count());
    }

    public function test_client_role_gets_403(): void
    {
        $client = User::factory()->create(['role' => 'user']);

        $response = $this->postJson('/api/v1/driver/orders', $this->payload(), $this->authHeaders($client));

        $response->assertForbidden();
        $this->assertSame(0, Order::count());
    }

    public function test_curb_order_shows_in_admin_orders_query_with_curb_label(): void
    {
        $car = Car::factory()->create();
        $driver = User::factory()->driver()->create(['current_car_id' => $car->id]);

        $this->postJson('/api/v1/driver/orders', $this->payload(), $this->authHeaders($driver))
            ->assertCreated();

        $paginator = AdminOrdersQuery::fromRequest(Request::create('/admin/order', 'GET'))->paginate();

        $this->assertSame(1, $paginator->total());
        $order = $paginator->items()[0];
        $this->assertSame(Order::SOURCE_CURB, $order->source);
        $this->assertSame('От борта', $order->source_label);
        $this->assertSame('От борта', Order::sourceLabels()[Order::SOURCE_CURB]);
    }
}
