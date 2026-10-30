<?php

namespace Tests\Feature\Api;

use App\Events\AdminAlert;
use App\Events\ClientDriverLocation;
use App\Events\ClientOrderUpdated;
use App\Events\OrderStatusChanged;
use App\Models\Car;
use App\Models\DriverLocation;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ClientRealtimeTest extends TestCase
{
    use RefreshDatabase;

    private function authHeaders(User $user): array
    {
        $token = auth('api')->login($user);

        return ['Authorization' => 'Bearer ' . $token];
    }

    public function test_location_update_broadcasts_driver_location_to_client_of_active_order(): void
    {
        Event::fake([ClientDriverLocation::class]);

        $client = User::factory()->create();
        $driver = User::factory()->create();
        $order = Order::factory()->create([
            'client_id' => $client->id,
            'driver_id' => $driver->id,
            'status' => Order::STATUS_ASSIGNED,
        ]);

        $response = $this->postJson('/api/location/update', [
            'lat' => 53.5210,
            'lng' => 30.2400,
            'isZakaz' => true,
            'orderId' => $order->id,
        ], $this->authHeaders($driver));

        $response->assertOk()->assertJson(['status' => 'success']);

        Event::assertDispatched(
            ClientDriverLocation::class,
            function (ClientDriverLocation $event) use ($client, $order) {
                $channels = $event->broadcastOn();
                $channel = is_array($channels) ? $channels[0] : $channels;

                return $event->clientId === $client->id
                    && $event->orderId === $order->id
                    && abs($event->lat - 53.5210) < 0.0001
                    && abs($event->lng - 30.2400) < 0.0001
                    && $event->broadcastAs() === 'driverLocation'
                    && $channel->name === 'user.' . $client->id;
            }
        );
    }

    public function test_location_update_without_active_order_broadcasts_nothing(): void
    {
        Event::fake([ClientDriverLocation::class]);

        $driver = User::factory()->create();
        // A finished order is not an active trip: no live marker.
        Order::factory()->create([
            'driver_id' => $driver->id,
            'status' => Order::STATUS_COMPLETED,
        ]);

        $response = $this->postJson('/api/location/update', [
            'lat' => 53.5210,
            'lng' => 30.2400,
        ], $this->authHeaders($driver));

        $response->assertOk();
        Event::assertNotDispatched(ClientDriverLocation::class);
    }

    public function test_client_orders_carry_trip_car_and_driver_location(): void
    {
        $client = User::factory()->create();
        $driver = User::factory()->create();

        // Legacy relation car (User::car() by cars.user_id), the
        // device binding car (users.current_car_id) and the trip
        // car (orders.car_id): three different cars, the trip car
        // must win in the client payload.
        $legacyCar = Car::create([
            'user_id' => $driver->id,
            'brand' => 'Legacy',
            'model' => 'One',
            'number_plate' => '1111 AA-1',
        ]);
        $boundCar = Car::create([
            'user_id' => $driver->id,
            'brand' => 'Bound',
            'model' => 'Two',
            'number_plate' => '2222 BB-2',
        ]);
        $tripCar = Car::create([
            'user_id' => $driver->id,
            'brand' => 'Trip',
            'model' => 'Three',
            'number_plate' => '3333 CC-3',
        ]);
        $driver->update(['current_car_id' => $boundCar->id]);

        $activeOrder = Order::factory()->create([
            'client_id' => $client->id,
            'driver_id' => $driver->id,
            'car_id' => $tripCar->id,
            'status' => Order::STATUS_IN_PROGRESS,
        ]);
        DriverLocation::create([
            'user_id' => $driver->id,
            'lat' => 53.5300,
            'lng' => 30.2500,
            'is_zakaz' => true,
            'order_id' => $activeOrder->id,
        ]);

        $response = $this->getJson('/api/orders', $this->authHeaders($client));

        $response->assertOk();
        $orders = collect($response->json('data'));
        $found = $orders->firstWhere('id', $activeOrder->id);

        $this->assertNotNull($found);
        $this->assertSame($tripCar->id, $found['driver']['car']['id']);
        $this->assertEqualsWithDelta(53.5300, (float) $found['driver_location']['lat'], 0.0001);
        $this->assertEqualsWithDelta(30.2500, (float) $found['driver_location']['lng'], 0.0001);
    }

    public function test_client_orders_fall_back_to_bound_car_and_skip_location_for_inactive_order(): void
    {
        $client = User::factory()->create();
        $driver = User::factory()->create();

        $legacyCar = Car::create([
            'user_id' => $driver->id,
            'brand' => 'Legacy',
            'model' => 'One',
            'number_plate' => '1111 AA-1',
        ]);
        $boundCar = Car::create([
            'user_id' => $driver->id,
            'brand' => 'Bound',
            'model' => 'Two',
            'number_plate' => '2222 BB-2',
        ]);
        $driver->update(['current_car_id' => $boundCar->id]);

        // No orders.car_id: the bound car (current_car_id) is the
        // car the client must see.
        $order = Order::factory()->create([
            'client_id' => $client->id,
            'driver_id' => $driver->id,
            'car_id' => null,
            'status' => Order::STATUS_COMPLETED,
        ]);
        DriverLocation::create([
            'user_id' => $driver->id,
            'lat' => 53.5300,
            'lng' => 30.2500,
            'is_zakaz' => false,
            'order_id' => null,
        ]);

        $response = $this->getJson('/api/orders', $this->authHeaders($client));

        $response->assertOk();
        $found = collect($response->json('data'))->firstWhere('id', $order->id);

        $this->assertNotNull($found);
        $this->assertSame($boundCar->id, $found['driver']['car']['id']);
        // Completed trip: no live position is attached.
        $this->assertArrayNotHasKey('driver_location', $found);
    }

    public function test_decline_broadcasts_order_updated_to_client(): void
    {
        Event::fake([ClientOrderUpdated::class, AdminAlert::class, OrderStatusChanged::class]);

        $client = User::factory()->create();
        $driver = User::factory()->create();
        $order = Order::factory()->create([
            'client_id' => $client->id,
            'driver_id' => $driver->id,
            'status' => Order::STATUS_SEARCHING,
        ]);

        $response = $this->postJson('/api/orders/declineOrderByDriver', [
            'orderId' => $order->id,
        ], $this->authHeaders($driver));

        $response->assertOk()->assertJson(['status' => true]);
        $this->assertNull($order->fresh()->driver_id);

        Event::assertDispatched(
            ClientOrderUpdated::class,
            fn (ClientOrderUpdated $event) => $event->order->id === $order->id
        );
    }
}
