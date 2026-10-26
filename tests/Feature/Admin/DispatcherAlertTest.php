<?php

namespace Tests\Feature\Admin;

use App\Events\AdminAlert;
use App\Models\Message;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class DispatcherAlertTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_new_order_dispatches_admin_alert(): void
    {
        Event::fake([AdminAlert::class]);

        $order = Order::factory()->create();

        Event::assertDispatched(
            AdminAlert::class,
            fn (AdminAlert $event) => $event->type === AdminAlert::TYPE_NEW_ORDER
                && $event->orderId === $order->id
                && $event->broadcastAs() === 'admin.alert'
                && $event->broadcastWith()['order_id'] === $order->id
        );
    }

    public function test_client_message_dispatches_admin_alert(): void
    {
        Event::fake([AdminAlert::class]);

        $message = Message::factory()->create([
            'type' => 'user',
            'message' => 'Здравствуйте, где моя машина?',
        ]);

        Event::assertDispatched(
            AdminAlert::class,
            fn (AdminAlert $event) => $event->type === AdminAlert::TYPE_NEW_MESSAGE
                && $event->messageId === $message->id
        );
    }

    public function test_admin_message_does_not_dispatch_new_message_alert(): void
    {
        Event::fake([AdminAlert::class]);

        Message::factory()->create(['type' => 'admin']);

        Event::assertNotDispatched(
            AdminAlert::class,
            fn (AdminAlert $event) => $event->type === AdminAlert::TYPE_NEW_MESSAGE
        );
    }

    public function test_guest_is_redirected_from_overdue_endpoint(): void
    {
        $response = $this->get('/admin/data/overdue-orders');

        $response->assertRedirect(route('login'));
    }

    public function test_overdue_endpoint_returns_only_overdue_orders(): void
    {
        Setting::set('driver_accept_seconds', '60');
        $driver = User::factory()->driver()->create();
        $overdue = Order::factory()->searching()->create([
            'driver_id' => $driver->id,
            'driver_offered_at' => now()->subSeconds(300),
        ]);
        Order::factory()->searching()->create([
            'driver_id' => $driver->id,
            'driver_offered_at' => now()->subSeconds(5),
        ]);

        $response = $this->actingAs($this->admin(), 'web')->get('/admin/data/overdue-orders');

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonPath('0.order_id', $overdue->id);
        $response->assertJsonPath('0.reason', 'no_response');
        $response->assertJsonStructure([
            ['order_id', 'reason', 'driver_name', 'from_address', 'to_address', 'waiting_seconds', 'url'],
        ]);
        $this->assertGreaterThanOrEqual(300, $response->json('0.waiting_seconds'));
    }

    public function test_overdue_endpoint_includes_declined_order(): void
    {
        Setting::set('driver_accept_seconds', '60');
        // Отказ: назначение снято, метка предложения сохранена.
        $declined = Order::factory()->searching()->create([
            'driver_id' => null,
            'driver_offered_at' => now()->subSeconds(120),
        ]);

        $response = $this->actingAs($this->admin(), 'web')->get('/admin/data/overdue-orders');

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonPath('0.order_id', $declined->id);
        $response->assertJsonPath('0.reason', 'declined');
        $this->assertNull($response->json('0.driver_name'));
        $this->assertGreaterThanOrEqual(120, $response->json('0.waiting_seconds'));
    }

    public function test_overdue_endpoint_excludes_fresh_broadcast_order(): void
    {
        Setting::set('driver_accept_seconds', '60');
        // Свежий broadcast-заказ: водителя нет и предложения не было.
        Order::factory()->searching()->create([
            'driver_id' => null,
            'driver_offered_at' => null,
        ]);

        $response = $this->actingAs($this->admin(), 'web')->get('/admin/data/overdue-orders');

        $response->assertOk();
        $response->assertJsonCount(0);
    }

    public function test_overdue_endpoint_excludes_manually_unassigned_order(): void
    {
        Event::fake();
        Setting::set('driver_accept_seconds', '60');
        $driver = User::factory()->driver()->create();
        $order = Order::factory()->searching()->create([
            'driver_id' => $driver->id,
            'driver_offered_at' => now()->subSeconds(300),
        ]);

        // Диспетчер сам снял водителя: админка сбрасывает и метку
        // предложения - заказ перестаёт быть проблемным для панели.
        $response = $this->actingAs($this->admin(), 'web')->put(route('order.update', $order), [
            'driver_id' => null,
        ]);
        $response->assertRedirect(route('order.index'));
        $this->assertNull($order->fresh()->driver_offered_at);

        $response = $this->actingAs($this->admin(), 'web')->get('/admin/data/overdue-orders');

        $response->assertOk();
        $response->assertJsonCount(0);
    }

    public function test_declined_order_leaves_overdue_endpoint_after_reassignment(): void
    {
        Event::fake();
        Setting::set('driver_accept_seconds', '60');
        $order = Order::factory()->searching()->create([
            'driver_id' => null,
            'driver_offered_at' => now()->subSeconds(120),
        ]);
        $newDriver = User::factory()->driver()->create();

        $response = $this->actingAs($this->admin(), 'web')->get('/admin/data/overdue-orders');
        $response->assertJsonCount(1);

        // Переназначение: водитель назначен, отсчёт предложения свежий -
        // заказ уходит из панели до следующего таймаута.
        $response = $this->actingAs($this->admin(), 'web')->put(route('order.update', $order), [
            'driver_id' => $newDriver->id,
        ]);
        $response->assertRedirect(route('order.index'));

        $response = $this->actingAs($this->admin(), 'web')->get('/admin/data/overdue-orders');
        $response->assertOk();
        $response->assertJsonCount(0);
    }

    public function test_store_with_driver_sets_offered_at(): void
    {
        Event::fake();
        $driver = User::factory()->driver()->create();

        $response = $this->actingAs($this->admin(), 'web')->post(route('order.store'), [
            'client_first_name' => 'Иван',
            'client_last_name' => 'Иванов',
            'client_phone' => '+375 29 000-00-00',
            'from_address' => 'г. Быхов, ул. Ленина, 1',
            'to_address' => 'г. Быхов, ул. Советская, 2',
            'driver_id' => $driver->id,
        ]);

        $response->assertRedirect(route('order.index'));
        $order = Order::first();
        $this->assertSame(Order::STATUS_SEARCHING, $order->status);
        $this->assertNotNull($order->driver_offered_at);
    }

    public function test_reassigning_driver_refreshes_offered_at(): void
    {
        Event::fake();
        $driverA = User::factory()->driver()->create();
        $driverB = User::factory()->driver()->create();
        $offeredAt = now()->subHour();
        $order = Order::factory()->searching()->create([
            'driver_id' => $driverA->id,
            'driver_offered_at' => $offeredAt,
        ]);

        $response = $this->actingAs($this->admin(), 'web')->put(route('order.update', $order), [
            'driver_id' => $driverB->id,
        ]);

        $response->assertRedirect(route('order.index'));
        $fresh = $order->fresh();
        $this->assertSame($driverB->id, (int) $fresh->driver_id);
        $this->assertTrue($fresh->driver_offered_at->greaterThan($offeredAt));
    }

    public function test_update_without_driver_change_keeps_offered_at(): void
    {
        Event::fake();
        $driver = User::factory()->driver()->create();
        $offeredAt = now()->subHour()->startOfSecond();
        $order = Order::factory()->searching()->create([
            'driver_id' => $driver->id,
            'driver_offered_at' => $offeredAt,
        ]);

        $response = $this->actingAs($this->admin(), 'web')->put(route('order.update', $order), [
            'comment' => 'Позвонить за 5 минут',
        ]);

        $response->assertRedirect(route('order.index'));
        $this->assertTrue($order->fresh()->driver_offered_at->equalTo($offeredAt));
    }
}
