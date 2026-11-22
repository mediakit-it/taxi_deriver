<?php

namespace Tests\Feature\Admin;

use App\Events\OrderSent;
use App\Models\DriverLocation;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Терминальные заказы (пакет замечаний Кати, 22.11.2026): у
 * завершённого и отменённого заказа водителя менять нельзя - ни
 * быстрым назначением из списка, ни сохранением формы
 * редактирования. Защита живёт в AssignOrderDriver: другой
 * водитель - отказ, тот же водитель (или по-прежнему без
 * водителя) - тихий no-op без событий, чтобы остальные поля
 * карточки терминального заказа сохранялись как раньше и
 * отменённый заказ без водителя не анонсировался заново всем.
 */
class AdminTerminalOrderAssignTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    private function putDriverOnline(User $driver): void
    {
        // Свежая геопозиция = водитель на линии
        // (DriverLocation::work_status по свежести updated_at).
        DriverLocation::create([
            'user_id' => $driver->id,
            'lat' => 53.5191,
            'lng' => 30.2415,
        ]);
    }

    public function test_completed_order_cannot_be_reassigned_via_quick_assign(): void
    {
        Event::fake();
        $currentDriver = User::factory()->driver()->create();
        $otherDriver = User::factory()->driver()->create();
        // Другой водитель свободен и на линии: без терминальной
        // защиты назначение прошло бы - отказ даёт именно она.
        $this->putDriverOnline($otherDriver);
        $order = Order::factory()->create([
            'status' => Order::STATUS_COMPLETED,
            'driver_id' => $currentDriver->id,
        ]);

        $response = $this->actingAs($this->admin(), 'web')
            ->postJson(route('order.assign-driver', $order), ['driver_id' => $otherDriver->id]);

        $response->assertStatus(422)
            ->assertJsonPath('ok', false)
            ->assertJsonPath('message', 'Заказ завершён - водителя менять нельзя.');

        $fresh = $order->fresh();
        $this->assertSame($currentDriver->id, (int) $fresh->driver_id);
        $this->assertSame(Order::STATUS_COMPLETED, $fresh->status);
    }

    public function test_canceled_order_cannot_be_assigned_via_quick_assign(): void
    {
        Event::fake();
        $driver = User::factory()->driver()->create();
        $this->putDriverOnline($driver);
        $order = Order::factory()->create([
            'status' => Order::STATUS_CANCELED,
            'driver_id' => null,
        ]);

        $response = $this->actingAs($this->admin(), 'web')
            ->postJson(route('order.assign-driver', $order), ['driver_id' => $driver->id]);

        $response->assertStatus(422)
            ->assertJsonPath('ok', false)
            ->assertJsonPath('message', 'Заказ отменён - водителя менять нельзя.');

        $fresh = $order->fresh();
        $this->assertNull($fresh->driver_id);
        $this->assertSame(Order::STATUS_CANCELED, $fresh->status);
    }

    public function test_update_completed_order_with_other_driver_is_rejected(): void
    {
        Event::fake();
        $currentDriver = User::factory()->driver()->create();
        $otherDriver = User::factory()->driver()->create();
        $this->putDriverOnline($otherDriver);
        $order = Order::factory()->create([
            'status' => Order::STATUS_COMPLETED,
            'driver_id' => $currentDriver->id,
        ]);

        $response = $this->actingAs($this->admin(), 'web')
            ->put(route('order.update', $order), [
                'driver_id' => $otherDriver->id,
                'price' => 12,
            ]);

        $response->assertRedirect(route('order.edit', $order->id));
        $response->assertSessionHas('error', 'Заказ завершён - водителя менять нельзя.');

        $fresh = $order->fresh();
        $this->assertSame($currentDriver->id, (int) $fresh->driver_id);
        $this->assertSame(Order::STATUS_COMPLETED, $fresh->status);
    }

    public function test_update_completed_order_with_same_driver_saves_other_fields(): void
    {
        Event::fake();
        $driver = User::factory()->driver()->create();
        $order = Order::factory()->create([
            'status' => Order::STATUS_COMPLETED,
            'driver_id' => $driver->id,
            'price' => 10,
        ]);

        $response = $this->actingAs($this->admin(), 'web')
            ->put(route('order.update', $order), [
                'driver_id' => $driver->id,
                'price' => 15.5,
            ]);

        $response->assertRedirect(route('order.index'));

        $fresh = $order->fresh();
        $this->assertSame($driver->id, (int) $fresh->driver_id);
        $this->assertSame(Order::STATUS_COMPLETED, $fresh->status);
        $this->assertEquals(15.5, (float) $fresh->price);
        Event::assertNotDispatched(OrderSent::class);
    }

    public function test_update_canceled_order_without_driver_does_not_reoffer(): void
    {
        Event::fake();
        $order = Order::factory()->create([
            'status' => Order::STATUS_CANCELED,
            'driver_id' => null,
        ]);

        $response = $this->actingAs($this->admin(), 'web')
            ->put(route('order.update', $order), [
                'driver_id' => '',
                'comment' => 'Уточнение диспетчера',
            ]);

        $response->assertRedirect(route('order.index'));

        $fresh = $order->fresh();
        $this->assertNull($fresh->driver_id);
        $this->assertSame(Order::STATUS_CANCELED, $fresh->status);
        $this->assertSame('Уточнение диспетчера', $fresh->comment);
        // Главное регрессионное поведение: сохранение карточки
        // отменённого заказа без водителя не анонсирует его заново.
        Event::assertNotDispatched(OrderSent::class);
    }

    public function test_edit_page_of_completed_order_shows_static_driver(): void
    {
        $driver = User::factory()->driver()->create();
        $order = Order::factory()->create([
            'status' => Order::STATUS_COMPLETED,
            'driver_id' => $driver->id,
        ]);

        $response = $this->actingAs($this->admin(), 'web')
            ->get(route('order.edit', $order));

        $response->assertOk();
        $response->assertSee('водителя менять нельзя');
        $response->assertSee($driver->first_name . ' ' . $driver->last_name);
        $response->assertDontSee('name="driver_id"', false);
    }
}
