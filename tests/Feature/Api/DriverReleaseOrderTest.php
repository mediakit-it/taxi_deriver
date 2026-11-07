<?php

namespace Tests\Feature\Api;

use App\Events\AdminAlert;
use App\Events\OrderStatusChanged;
use App\Models\Car;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class DriverReleaseOrderTest extends TestCase
{
    use RefreshDatabase;

    private function authHeaders(User $user): array
    {
        $token = auth('api')->login($user);

        return ['Authorization' => 'Bearer '.$token];
    }

    public function test_driver_releases_assigned_order_back_to_search(): void
    {
        Event::fake();
        $car = Car::factory()->create();
        $driver = User::factory()->driver()->create(['current_car_id' => $car->id]);
        $order = Order::factory()->create([
            'status' => Order::STATUS_ASSIGNED,
            'driver_id' => $driver->id,
            'car_id' => $car->id,
        ]);

        $response = $this->postJson('/api/v1/driver/orders/release', [
            'order_id' => $order->id,
        ], $this->authHeaders($driver));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('order.status', Order::STATUS_SEARCHING_OTHER);

        // The order is back to searching for another driver: driver
        // and car are detached, and the "was with a specific driver"
        // marker is set - the dispatcher attention panel tells a
        // released order apart from a fresh broadcast order by it
        // (Order::scopeDeclinedByDriver).
        $order->refresh();
        $this->assertSame(Order::STATUS_SEARCHING_OTHER, $order->status);
        $this->assertNull($order->driver_id);
        $this->assertNull($order->car_id);
        $this->assertNotNull($order->driver_offered_at);
        $this->assertTrue(Order::declinedByDriver()->whereKey($order->id)->exists());

        // The dispatcher is signalled at once, exactly as on a
        // declined offer.
        Event::assertDispatched(AdminAlert::class, fn (AdminAlert $event) => $event->type === AdminAlert::TYPE_DRIVER_DECLINED
            && $event->orderId === $order->id);
        Event::assertDispatched(OrderStatusChanged::class, fn (OrderStatusChanged $event) => $event->reason === 'driver_declined'
            && $event->order->id === $order->id);
    }

    public function test_driver_releases_order_on_place(): void
    {
        Event::fake();
        $driver = User::factory()->driver()->create();
        $order = Order::factory()->create([
            'status' => Order::STATUS_ON_PLACE,
            'driver_id' => $driver->id,
        ]);

        $response = $this->postJson('/api/v1/driver/orders/release', [
            'order_id' => $order->id,
        ], $this->authHeaders($driver));

        $response->assertOk()->assertJsonPath('success', true);

        $order->refresh();
        $this->assertSame(Order::STATUS_SEARCHING_OTHER, $order->status);
        $this->assertNull($order->driver_id);
    }

    public function test_release_in_progress_order_gets_422(): void
    {
        Event::fake();
        $driver = User::factory()->driver()->create();
        $order = Order::factory()->create([
            'status' => Order::STATUS_IN_PROGRESS,
            'driver_id' => $driver->id,
        ]);

        $response = $this->postJson('/api/v1/driver/orders/release', [
            'order_id' => $order->id,
        ], $this->authHeaders($driver));

        $response->assertStatus(422)->assertJsonPath('success', false);

        // The trip goes on untouched.
        $order->refresh();
        $this->assertSame(Order::STATUS_IN_PROGRESS, $order->status);
        $this->assertSame($driver->id, (int) $order->driver_id);
    }

    public function test_release_completed_order_gets_422(): void
    {
        Event::fake();
        $driver = User::factory()->driver()->create();
        $order = Order::factory()->create([
            'status' => Order::STATUS_COMPLETED,
            'driver_id' => $driver->id,
        ]);

        $response = $this->postJson('/api/v1/driver/orders/release', [
            'order_id' => $order->id,
        ], $this->authHeaders($driver));

        $response->assertStatus(422)->assertJsonPath('success', false);

        $order->refresh();
        $this->assertSame(Order::STATUS_COMPLETED, $order->status);
        $this->assertSame($driver->id, (int) $order->driver_id);
    }

    public function test_another_driver_cannot_release_order(): void
    {
        Event::fake();
        $driver = User::factory()->driver()->create();
        $otherDriver = User::factory()->driver()->create();
        $order = Order::factory()->create([
            'status' => Order::STATUS_ASSIGNED,
            'driver_id' => $driver->id,
        ]);

        $response = $this->postJson('/api/v1/driver/orders/release', [
            'order_id' => $order->id,
        ], $this->authHeaders($otherDriver));

        $response->assertStatus(403)->assertJsonPath('success', false);

        $order->refresh();
        $this->assertSame(Order::STATUS_ASSIGNED, $order->status);
        $this->assertSame($driver->id, (int) $order->driver_id);
    }

    public function test_client_role_gets_403(): void
    {
        Event::fake();
        $driver = User::factory()->driver()->create();
        $client = User::factory()->create(['role' => 'user']);
        $order = Order::factory()->create([
            'status' => Order::STATUS_ASSIGNED,
            'driver_id' => $driver->id,
        ]);

        $response = $this->postJson('/api/v1/driver/orders/release', [
            'order_id' => $order->id,
        ], $this->authHeaders($client));

        $response->assertStatus(403);

        $order->refresh();
        $this->assertSame(Order::STATUS_ASSIGNED, $order->status);
        $this->assertSame($driver->id, (int) $order->driver_id);
    }

    public function test_release_requires_authentication(): void
    {
        $order = Order::factory()->create([
            'status' => Order::STATUS_ASSIGNED,
        ]);

        $response = $this->postJson('/api/v1/driver/orders/release', [
            'order_id' => $order->id,
        ]);

        $response->assertStatus(401);
    }

    public function test_release_missing_order_returns_404(): void
    {
        Event::fake();
        $driver = User::factory()->driver()->create();

        $response = $this->postJson('/api/v1/driver/orders/release', [
            'order_id' => 999999,
        ], $this->authHeaders($driver));

        $response->assertStatus(404)->assertJsonPath('success', false);
    }
}
