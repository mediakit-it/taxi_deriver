<?php

namespace Tests\Feature\Api;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class DriverOrderActionsTest extends TestCase
{
    use RefreshDatabase;

    private function authHeaders(User $user): array
    {
        $token = auth('api')->login($user);

        return ['Authorization' => 'Bearer ' . $token];
    }

    public function test_driver_can_decline_unassigned_order(): void
    {
        Event::fake();
        $driver = User::factory()->create();
        $order = Order::factory()->create([
            'status' => Order::STATUS_PENDING,
            'driver_id' => null,
        ]);

        $response = $this->postJson('/api/orders/declineOrderByDriver', [
            'orderId' => $order->id,
        ], $this->authHeaders($driver));

        $response->assertOk()->assertJson(['status' => true]);

        // The decline does not take the order: it stays available to others.
        $order->refresh();
        $this->assertSame(Order::STATUS_PENDING, $order->status);
        $this->assertNull($order->driver_id);
    }

    public function test_driver_cannot_decline_order_assigned_to_another_driver(): void
    {
        Event::fake();
        $driver = User::factory()->create();
        $otherDriver = User::factory()->create();
        $order = Order::factory()->create([
            'status' => Order::STATUS_ASSIGNED,
            'driver_id' => $otherDriver->id,
        ]);

        $response = $this->postJson('/api/orders/declineOrderByDriver', [
            'orderId' => $order->id,
        ], $this->authHeaders($driver));

        $response->assertOk()->assertJson(['status' => false]);

        $order->refresh();
        $this->assertSame($otherDriver->id, $order->driver_id);
        $this->assertSame(Order::STATUS_ASSIGNED, $order->status);
    }

    public function test_decline_requires_authentication(): void
    {
        $order = Order::factory()->create(['status' => Order::STATUS_PENDING]);

        $response = $this->postJson('/api/orders/declineOrderByDriver', [
            'orderId' => $order->id,
        ]);

        $response->assertUnauthorized();
    }

    public function test_decline_missing_order_returns_404(): void
    {
        Event::fake();
        $driver = User::factory()->create();

        $response = $this->postJson('/api/orders/declineOrderByDriver', [
            'orderId' => 999999,
        ], $this->authHeaders($driver));

        $response->assertNotFound()->assertJson(['status' => false]);
    }

    public function test_driver_moves_own_order_through_trip_stages(): void
    {
        Event::fake();
        $driver = User::factory()->create();
        $order = Order::factory()->create([
            'status' => Order::STATUS_ASSIGNED,
            'driver_id' => $driver->id,
        ]);

        $response = $this->postJson('/api/orders/onPlace', [
            'orderId' => $order->id,
        ], $this->authHeaders($driver));
        $response->assertOk()->assertJson(['status' => true]);
        $this->assertSame(Order::STATUS_ON_PLACE, $order->refresh()->status);

        $response = $this->postJson('/api/orders/startRide', [
            'orderId' => $order->id,
        ], $this->authHeaders($driver));
        $response->assertOk()->assertJson(['status' => true]);
        $this->assertSame(Order::STATUS_IN_PROGRESS, $order->refresh()->status);
    }

    public function test_driver_cannot_move_another_drivers_order(): void
    {
        Event::fake();
        $driver = User::factory()->create();
        $otherDriver = User::factory()->create();
        $order = Order::factory()->create([
            'status' => Order::STATUS_ASSIGNED,
            'driver_id' => $otherDriver->id,
        ]);

        $response = $this->postJson('/api/orders/onPlace', [
            'orderId' => $order->id,
        ], $this->authHeaders($driver));
        $response->assertForbidden()->assertJson(['status' => false]);
        $this->assertSame(Order::STATUS_ASSIGNED, $order->refresh()->status);

        $response = $this->postJson('/api/orders/startRide', [
            'orderId' => $order->id,
        ], $this->authHeaders($driver));
        $response->assertForbidden()->assertJson(['status' => false]);
        $this->assertSame(Order::STATUS_ASSIGNED, $order->refresh()->status);
    }
}
