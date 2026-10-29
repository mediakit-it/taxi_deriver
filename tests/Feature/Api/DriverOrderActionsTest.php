<?php

namespace Tests\Feature\Api;

use App\Events\AdminAlert;
use App\Models\Car;
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

    public function test_driver_can_accept_own_assigned_offer(): void
    {
        Event::fake();
        $driver = User::factory()->create();
        $order = Order::factory()->create([
            'status' => Order::STATUS_SEARCHING,
            'driver_id' => $driver->id,
            'driver_offered_at' => now()->subSeconds(30),
        ]);

        $response = $this->postJson('/api/orders/acceptOrderByDriver', [
            'orderId' => $order->id,
        ], $this->authHeaders($driver));

        $response->assertOk()->assertJson(['status' => true]);

        // Accepting a personal offer confirms the same driver, not a claim.
        $order->refresh();
        $this->assertSame(Order::STATUS_ASSIGNED, $order->status);
        $this->assertSame($driver->id, $order->driver_id);
    }

    public function test_driver_cannot_accept_offer_assigned_to_another_driver(): void
    {
        Event::fake();
        $driver = User::factory()->create();
        $otherDriver = User::factory()->create();
        $order = Order::factory()->create([
            'status' => Order::STATUS_SEARCHING,
            'driver_id' => $otherDriver->id,
            'driver_offered_at' => now()->subSeconds(30),
        ]);

        $response = $this->postJson('/api/orders/acceptOrderByDriver', [
            'orderId' => $order->id,
        ], $this->authHeaders($driver));

        $response->assertOk()->assertJson(['status' => false]);

        $order->refresh();
        $this->assertSame($otherDriver->id, $order->driver_id);
        $this->assertSame(Order::STATUS_SEARCHING, $order->status);
    }

    public function test_driver_can_decline_own_assigned_offer(): void
    {
        Event::fake();
        $driver = User::factory()->create();
        $order = Order::factory()->create([
            'status' => Order::STATUS_SEARCHING,
            'driver_id' => $driver->id,
            'driver_offered_at' => now()->subSeconds(30),
        ]);

        $response = $this->postJson('/api/orders/declineOrderByDriver', [
            'orderId' => $order->id,
        ], $this->authHeaders($driver));

        $response->assertOk()->assertJson(['status' => true]);

        // The assignment is released; the order itself keeps searching.
        // driver_offered_at is KEPT: it is the "was offered" evidence the
        // dispatcher attention panel uses to tell a declined order apart
        // from a fresh broadcast order (Order::scopeDeclinedByDriver).
        $order->refresh();
        $this->assertNull($order->driver_id);
        $this->assertNotNull($order->driver_offered_at);
        $this->assertSame(Order::STATUS_SEARCHING, $order->status);

        // The dispatcher is signalled at once that a new driver is needed.
        Event::assertDispatched(AdminAlert::class, fn (AdminAlert $event) => $event->type === AdminAlert::TYPE_DRIVER_DECLINED
            && $event->orderId === $order->id);
    }

    public function test_accept_writes_bound_car_id_to_order(): void
    {
        Event::fake();
        $driver = User::factory()->create();
        $car = Car::factory()->create();
        $order = Order::factory()->create([
            'status' => Order::STATUS_PENDING,
            'driver_id' => null,
        ]);

        $response = $this->postJson('/api/orders/acceptOrderByDriver', [
            'orderId' => $order->id,
            'car_id' => $car->id,
        ], $this->authHeaders($driver));

        $response->assertOk()->assertJson(['status' => true]);

        $order->refresh();
        $this->assertSame(Order::STATUS_ASSIGNED, $order->status);
        $this->assertSame($car->id, $order->car_id);
    }

    public function test_accept_own_offer_writes_bound_car_id(): void
    {
        Event::fake();
        $driver = User::factory()->create();
        $car = Car::factory()->create();
        $order = Order::factory()->create([
            'status' => Order::STATUS_SEARCHING,
            'driver_id' => $driver->id,
            'driver_offered_at' => now()->subSeconds(30),
        ]);

        $response = $this->postJson('/api/orders/acceptOrderByDriver', [
            'orderId' => $order->id,
            'car_id' => $car->id,
        ], $this->authHeaders($driver));

        $response->assertOk()->assertJson(['status' => true]);
        $this->assertSame($car->id, $order->refresh()->car_id);
    }

    public function test_accept_without_car_id_leaves_order_car_empty(): void
    {
        Event::fake();
        $driver = User::factory()->create();
        $order = Order::factory()->create([
            'status' => Order::STATUS_PENDING,
            'driver_id' => null,
        ]);

        $response = $this->postJson('/api/orders/acceptOrderByDriver', [
            'orderId' => $order->id,
        ], $this->authHeaders($driver));

        $response->assertOk()->assertJson(['status' => true]);
        $this->assertNull($order->refresh()->car_id);
    }

    public function test_accept_with_unknown_car_id_is_rejected(): void
    {
        Event::fake();
        $driver = User::factory()->create();
        $order = Order::factory()->create([
            'status' => Order::STATUS_PENDING,
            'driver_id' => null,
        ]);

        $response = $this->postJson('/api/orders/acceptOrderByDriver', [
            'orderId' => $order->id,
            'car_id' => 999999,
        ], $this->authHeaders($driver));

        $response->assertUnprocessable()->assertJson(['status' => false]);

        // Заказ не тронут: ни водителя, ни авто, ни статуса.
        $order->refresh();
        $this->assertSame(Order::STATUS_PENDING, $order->status);
        $this->assertNull($order->driver_id);
        $this->assertNull($order->car_id);
    }

    public function test_start_ride_writes_bound_car_id_when_order_has_none(): void
    {
        Event::fake();
        $driver = User::factory()->create();
        $car = Car::factory()->create();
        $order = Order::factory()->create([
            'status' => Order::STATUS_ON_PLACE,
            'driver_id' => $driver->id,
        ]);

        $response = $this->postJson('/api/orders/startRide', [
            'orderId' => $order->id,
            'car_id' => $car->id,
        ], $this->authHeaders($driver));

        $response->assertOk()->assertJson(['status' => true]);

        $order->refresh();
        $this->assertSame(Order::STATUS_IN_PROGRESS, $order->status);
        $this->assertSame($car->id, $order->car_id);
    }

    public function test_driver_cannot_decline_offer_assigned_to_another_driver(): void
    {
        Event::fake();
        $driver = User::factory()->create();
        $otherDriver = User::factory()->create();
        $order = Order::factory()->create([
            'status' => Order::STATUS_SEARCHING,
            'driver_id' => $otherDriver->id,
            'driver_offered_at' => now()->subSeconds(30),
        ]);

        $response = $this->postJson('/api/orders/declineOrderByDriver', [
            'orderId' => $order->id,
        ], $this->authHeaders($driver));

        $response->assertOk()->assertJson(['status' => false]);

        $order->refresh();
        $this->assertSame($otherDriver->id, $order->driver_id);
        $this->assertSame(Order::STATUS_SEARCHING, $order->status);
        $this->assertNotNull($order->driver_offered_at);
    }

    public function test_driver_with_assigned_order_cannot_claim_second_pending_order(): void
    {
        Event::fake();
        $driver = User::factory()->create();
        $activeOrder = Order::factory()->create([
            'status' => Order::STATUS_ASSIGNED,
            'driver_id' => $driver->id,
        ]);
        $secondOrder = Order::factory()->create([
            'status' => Order::STATUS_PENDING,
            'driver_id' => null,
        ]);

        $response = $this->postJson('/api/orders/acceptOrderByDriver', [
            'orderId' => $secondOrder->id,
        ], $this->authHeaders($driver));

        $response->assertOk()->assertJson([
            'status' => false,
            'message' => 'У вас уже есть активный заказ',
        ]);

        // The second order stays unclaimed; the active one is untouched.
        $secondOrder->refresh();
        $this->assertSame(Order::STATUS_PENDING, $secondOrder->status);
        $this->assertNull($secondOrder->driver_id);
        $this->assertSame(Order::STATUS_ASSIGNED, $activeOrder->refresh()->status);
    }

    public function test_driver_with_in_progress_order_cannot_accept_offered_searching_order(): void
    {
        Event::fake();
        $driver = User::factory()->create();
        Order::factory()->create([
            'status' => Order::STATUS_IN_PROGRESS,
            'driver_id' => $driver->id,
        ]);
        $offeredOrder = Order::factory()->create([
            'status' => Order::STATUS_SEARCHING,
            'driver_id' => $driver->id,
            'driver_offered_at' => now()->subSeconds(30),
        ]);

        $response = $this->postJson('/api/orders/acceptOrderByDriver', [
            'orderId' => $offeredOrder->id,
        ], $this->authHeaders($driver));

        $response->assertOk()->assertJson([
            'status' => false,
            'message' => 'У вас уже есть активный заказ',
        ]);

        // The offer stays open (searching); it was not silently accepted.
        $offeredOrder->refresh();
        $this->assertSame(Order::STATUS_SEARCHING, $offeredOrder->status);
        $this->assertSame($driver->id, $offeredOrder->driver_id);
    }

    public function test_driver_with_active_order_cannot_claim_via_add_driving(): void
    {
        Event::fake();
        $driver = User::factory()->create();
        Order::factory()->create([
            'status' => Order::STATUS_ON_PLACE,
            'driver_id' => $driver->id,
        ]);
        $secondOrder = Order::factory()->create([
            'status' => Order::STATUS_PENDING,
            'driver_id' => null,
        ]);

        $response = $this->postJson('/api/orders/add', [
            'orderId' => $secondOrder->id,
        ], $this->authHeaders($driver));

        $response->assertOk()->assertJson([
            'status' => false,
            'message' => 'У вас уже есть активный заказ',
        ]);

        $secondOrder->refresh();
        $this->assertSame(Order::STATUS_PENDING, $secondOrder->status);
        $this->assertNull($secondOrder->driver_id);
    }

    public function test_driver_without_active_order_claims_via_add_driving(): void
    {
        Event::fake();
        $driver = User::factory()->create();
        $order = Order::factory()->create([
            'status' => Order::STATUS_PENDING,
            'driver_id' => null,
        ]);

        $response = $this->postJson('/api/orders/add', [
            'orderId' => $order->id,
        ], $this->authHeaders($driver));

        $response->assertOk()->assertJson(['status' => true]);

        $order->refresh();
        $this->assertSame(Order::STATUS_ASSIGNED, $order->status);
        $this->assertSame($driver->id, $order->driver_id);
    }

    public function test_driver_reaccepting_own_assigned_order_is_idempotent(): void
    {
        Event::fake();
        $driver = User::factory()->create();
        $order = Order::factory()->create([
            'status' => Order::STATUS_ASSIGNED,
            'driver_id' => $driver->id,
        ]);

        $response = $this->postJson('/api/orders/acceptOrderByDriver', [
            'orderId' => $order->id,
        ], $this->authHeaders($driver));

        $response->assertOk()->assertJson(['status' => true]);

        $order->refresh();
        $this->assertSame(Order::STATUS_ASSIGNED, $order->status);
        $this->assertSame($driver->id, $order->driver_id);
    }

    public function test_close_driving_with_canceled_returns_status_true(): void
    {
        Event::fake();
        $driver = User::factory()->create();
        $order = Order::factory()->create([
            'status' => Order::STATUS_IN_PROGRESS,
            'driver_id' => $driver->id,
        ]);

        $response = $this->postJson('/api/orders/close', [
            'orderId' => $order->id,
            'zakaz' => Order::STATUS_CANCELED,
            'time' => 300,
        ], $this->authHeaders($driver));

        // The cancel WAS saved on the server, so the answer is status=true:
        // status=false would tell the app the close failed (zombie trip).
        $response->assertOk()->assertJson(['status' => true]);
        $this->assertSame(Order::STATUS_CANCELED, $order->refresh()->status);
    }

    public function test_close_driving_with_completed_returns_status_true(): void
    {
        Event::fake();
        $driver = User::factory()->create();
        $order = Order::factory()->create([
            'status' => Order::STATUS_IN_PROGRESS,
            'driver_id' => $driver->id,
        ]);

        $response = $this->postJson('/api/orders/close', [
            'orderId' => $order->id,
            'zakaz' => Order::STATUS_COMPLETED,
            'time' => 300,
        ], $this->authHeaders($driver));

        $response->assertOk()->assertJson(['status' => true]);
        $this->assertSame(Order::STATUS_COMPLETED, $order->refresh()->status);
    }
}
