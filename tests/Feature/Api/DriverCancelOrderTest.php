<?php

namespace Tests\Feature\Api;

use App\Events\AdminAlert;
use App\Events\OrderStatusChanged;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

// Отмена заказа в поездке с причиной (пакет замечаний 22.11.2026):
// исход решает пара «источник + причина». «Отказ клиента» и любой
// заказ «от борта» закрываются отменёнными; остальные причины у
// заказов из приложения и по телефону возвращают заказ в поиск
// другого водителя, зеркально отпусканию (DriverReleaseOrderTest).
class DriverCancelOrderTest extends TestCase
{
    use RefreshDatabase;

    private function authHeaders(User $user): array
    {
        $token = auth('api')->login($user);

        return ['Authorization' => 'Bearer '.$token];
    }

    public function test_client_refusal_cancels_app_order_and_keeps_driver(): void
    {
        Event::fake();
        $driver = User::factory()->driver()->create();
        $order = Order::factory()->create([
            'status' => Order::STATUS_IN_PROGRESS,
            'source' => Order::SOURCE_APP,
            'driver_id' => $driver->id,
        ]);

        $response = $this->postJson('/api/v1/driver/orders/cancel', [
            'order_id' => $order->id,
            'reason' => Order::CANCEL_REASON_CLIENT_REFUSAL,
            'comment' => 'Пассажир передумал ехать',
        ], $this->authHeaders($driver));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('order.status', Order::STATUS_CANCELED)
            ->assertJsonPath('order.cancel_reason', Order::CANCEL_REASON_CLIENT_REFUSAL);

        $order->refresh();
        $this->assertSame(Order::STATUS_CANCELED, $order->status);
        $this->assertSame(Order::CANCEL_REASON_CLIENT_REFUSAL, $order->cancel_reason);
        $this->assertSame('Пассажир передумал ехать', $order->cancel_comment);
        // Водитель остаётся в заказе: это история уже начатой поездки.
        $this->assertSame($driver->id, (int) $order->driver_id);

        Event::assertDispatched(OrderStatusChanged::class, fn (OrderStatusChanged $event) => $event->reason === 'driver_canceled'
            && $event->order->id === $order->id);
    }

    public function test_tech_problems_returns_phone_order_back_to_search(): void
    {
        Event::fake();
        $driver = User::factory()->driver()->create();
        $order = Order::factory()->create([
            'status' => Order::STATUS_IN_PROGRESS,
            'source' => Order::SOURCE_PHONE,
            'driver_id' => $driver->id,
        ]);

        $response = $this->postJson('/api/v1/driver/orders/cancel', [
            'order_id' => $order->id,
            'reason' => Order::CANCEL_REASON_TECH_PROBLEMS,
            'comment' => 'Заглох двигатель',
        ], $this->authHeaders($driver));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('order.status', Order::STATUS_SEARCHING_OTHER);

        // Заказ свободен для нового водителя, как после отпускания:
        // водитель и авто сняты, метка предложения выставлена, а
        // причина и комментарий сохранены для диспетчера.
        $order->refresh();
        $this->assertSame(Order::STATUS_SEARCHING_OTHER, $order->status);
        $this->assertNull($order->driver_id);
        $this->assertNull($order->car_id);
        $this->assertNotNull($order->driver_offered_at);
        $this->assertSame(Order::CANCEL_REASON_TECH_PROBLEMS, $order->cancel_reason);
        $this->assertSame('Заглох двигатель', $order->cancel_comment);
        $this->assertTrue(Order::declinedByDriver()->whereKey($order->id)->exists());

        Event::assertDispatched(AdminAlert::class, fn (AdminAlert $event) => $event->type === AdminAlert::TYPE_DRIVER_DECLINED
            && $event->orderId === $order->id);
        Event::assertDispatched(OrderStatusChanged::class, fn (OrderStatusChanged $event) => $event->reason === 'driver_declined'
            && $event->order->id === $order->id);
    }

    public function test_curb_order_is_canceled_even_with_tech_problems(): void
    {
        Event::fake();
        $driver = User::factory()->driver()->create();
        $order = Order::factory()->create([
            'status' => Order::STATUS_IN_PROGRESS,
            'source' => Order::SOURCE_CURB,
            'client_id' => null,
            'driver_id' => $driver->id,
        ]);

        $response = $this->postJson('/api/v1/driver/orders/cancel', [
            'order_id' => $order->id,
            'reason' => Order::CANCEL_REASON_TECH_PROBLEMS,
            'comment' => 'Спустило колесо',
        ], $this->authHeaders($driver));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('order.status', Order::STATUS_CANCELED);

        // Заказ от борта не возвращается в поиск: клиента в системе
        // у него нет. Заказ остаётся отменённым с причиной - и
        // виден в админке (см. AdminAssignDriverGuardTest про
        // выборку отменённых в AdminOrdersQuery).
        $order->refresh();
        $this->assertSame(Order::STATUS_CANCELED, $order->status);
        $this->assertSame(Order::CANCEL_REASON_TECH_PROBLEMS, $order->cancel_reason);
        $this->assertSame($driver->id, (int) $order->driver_id);
    }

    public function test_missing_comment_gets_422(): void
    {
        Event::fake();
        $driver = User::factory()->driver()->create();
        $order = Order::factory()->create([
            'status' => Order::STATUS_IN_PROGRESS,
            'driver_id' => $driver->id,
        ]);

        $response = $this->postJson('/api/v1/driver/orders/cancel', [
            'order_id' => $order->id,
            'reason' => Order::CANCEL_REASON_OTHER,
        ], $this->authHeaders($driver));

        $response->assertStatus(422)
            ->assertJsonValidationErrors('comment');

        $order->refresh();
        $this->assertSame(Order::STATUS_IN_PROGRESS, $order->status);
        $this->assertNull($order->cancel_reason);
    }

    public function test_invalid_reason_gets_422(): void
    {
        Event::fake();
        $driver = User::factory()->driver()->create();
        $order = Order::factory()->create([
            'status' => Order::STATUS_IN_PROGRESS,
            'driver_id' => $driver->id,
        ]);

        $response = $this->postJson('/api/v1/driver/orders/cancel', [
            'order_id' => $order->id,
            'reason' => 'bad_mood',
            'comment' => 'Не хочу ехать',
        ], $this->authHeaders($driver));

        $response->assertStatus(422)
            ->assertJsonValidationErrors('reason');

        $order->refresh();
        $this->assertSame(Order::STATUS_IN_PROGRESS, $order->status);
    }

    public function test_cancel_before_trip_gets_422(): void
    {
        Event::fake();
        $driver = User::factory()->driver()->create();
        $order = Order::factory()->create([
            'status' => Order::STATUS_ASSIGNED,
            'driver_id' => $driver->id,
        ]);

        $response = $this->postJson('/api/v1/driver/orders/cancel', [
            'order_id' => $order->id,
            'reason' => Order::CANCEL_REASON_OTHER,
            'comment' => 'Не могу ехать',
        ], $this->authHeaders($driver));

        $response->assertStatus(422)->assertJsonPath('success', false);

        // До поездки действует отпускание (release), заказ не тронут.
        $order->refresh();
        $this->assertSame(Order::STATUS_ASSIGNED, $order->status);
        $this->assertSame($driver->id, (int) $order->driver_id);
        $this->assertNull($order->cancel_reason);
    }

    public function test_another_driver_cannot_cancel_order(): void
    {
        Event::fake();
        $driver = User::factory()->driver()->create();
        $otherDriver = User::factory()->driver()->create();
        $order = Order::factory()->create([
            'status' => Order::STATUS_IN_PROGRESS,
            'driver_id' => $driver->id,
        ]);

        $response = $this->postJson('/api/v1/driver/orders/cancel', [
            'order_id' => $order->id,
            'reason' => Order::CANCEL_REASON_OTHER,
            'comment' => 'Чужой заказ',
        ], $this->authHeaders($otherDriver));

        $response->assertStatus(403)->assertJsonPath('success', false);

        $order->refresh();
        $this->assertSame(Order::STATUS_IN_PROGRESS, $order->status);
        $this->assertNull($order->cancel_reason);
    }

    public function test_client_role_gets_403(): void
    {
        Event::fake();
        $driver = User::factory()->driver()->create();
        $client = User::factory()->create(['role' => 'user']);
        $order = Order::factory()->create([
            'status' => Order::STATUS_IN_PROGRESS,
            'driver_id' => $driver->id,
        ]);

        $response = $this->postJson('/api/v1/driver/orders/cancel', [
            'order_id' => $order->id,
            'reason' => Order::CANCEL_REASON_CLIENT_REFUSAL,
            'comment' => 'Отказ',
        ], $this->authHeaders($client));

        $response->assertStatus(403);

        $order->refresh();
        $this->assertSame(Order::STATUS_IN_PROGRESS, $order->status);
    }

    public function test_cancel_requires_authentication(): void
    {
        $order = Order::factory()->create([
            'status' => Order::STATUS_IN_PROGRESS,
        ]);

        $response = $this->postJson('/api/v1/driver/orders/cancel', [
            'order_id' => $order->id,
            'reason' => Order::CANCEL_REASON_OTHER,
            'comment' => 'Без входа',
        ]);

        $response->assertStatus(401);
    }
}
