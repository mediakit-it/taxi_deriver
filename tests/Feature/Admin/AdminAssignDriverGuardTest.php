<?php

namespace Tests\Feature\Admin;

use App\Models\DriverLocation;
use App\Models\Order;
use App\Models\User;
use App\Queries\AdminOrdersQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Защита назначения водителя (пакет замечаний 22.11.2026): занятого
 * активным заказом и водителя не на линии назначить нельзя - раньше
 * звонок уходил занятому, а принятие падало с ошибкой уже в
 * приложении водителя. Защита живёт в AssignOrderDriver, здесь -
 * поведение быстрого назначения из списка заказов.
 */
class AdminAssignDriverGuardTest extends TestCase
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

    public function test_busy_driver_cannot_be_assigned(): void
    {
        Event::fake();
        $busyDriver = User::factory()->driver()->create();
        $this->putDriverOnline($busyDriver);
        Order::factory()->create([
            'status' => Order::STATUS_ASSIGNED,
            'driver_id' => $busyDriver->id,
        ]);
        $order = Order::factory()->create([
            'status' => Order::STATUS_PENDING,
            'driver_id' => null,
        ]);

        $response = $this->actingAs($this->admin(), 'web')
            ->postJson(route('order.assign-driver', $order), ['driver_id' => $busyDriver->id]);

        $response->assertStatus(422)
            ->assertJsonPath('ok', false);

        $this->assertNull($order->fresh()->driver_id);
    }

    public function test_offline_driver_cannot_be_assigned(): void
    {
        Event::fake();
        // Ни одной строки геопозиции: водитель не на линии.
        $offlineDriver = User::factory()->driver()->create();
        $order = Order::factory()->create([
            'status' => Order::STATUS_PENDING,
            'driver_id' => null,
        ]);

        $response = $this->actingAs($this->admin(), 'web')
            ->postJson(route('order.assign-driver', $order), ['driver_id' => $offlineDriver->id]);

        $response->assertStatus(422)
            ->assertJsonPath('ok', false);

        $this->assertNull($order->fresh()->driver_id);
    }

    public function test_online_free_driver_can_be_assigned(): void
    {
        Event::fake();
        $driver = User::factory()->driver()->create();
        $this->putDriverOnline($driver);
        $order = Order::factory()->create([
            'status' => Order::STATUS_PENDING,
            'driver_id' => null,
        ]);

        $response = $this->actingAs($this->admin(), 'web')
            ->postJson(route('order.assign-driver', $order), ['driver_id' => $driver->id]);

        $response->assertOk()->assertJson(['ok' => true]);
        $this->assertSame($driver->id, (int) $order->fresh()->driver_id);
    }

    public function test_canceled_curb_order_is_returned_by_canceled_filter(): void
    {
        // Отменённый заказ от борта не должен «пропадать» из
        // админки: запрос списка с фильтром по отменённым обязан
        // его отдавать (приоритет у отменённых теперь общий с
        // завершёнными - одна группа в конце списка).
        $order = Order::factory()->create([
            'status' => Order::STATUS_CANCELED,
            'source' => Order::SOURCE_CURB,
            'client_id' => null,
            'cancel_reason' => Order::CANCEL_REASON_TECH_PROBLEMS,
            'cancel_comment' => 'Спустило колесо',
        ]);

        $request = Request::create('/admin/orders', 'GET', [
            'status' => Order::STATUS_CANCELED,
        ]);
        $page = AdminOrdersQuery::fromRequest($request)->paginate();

        $this->assertTrue(
            $page->getCollection()->contains('id', $order->id),
            'Отменённый заказ от борта не попал в выборку отменённых',
        );
    }
}
