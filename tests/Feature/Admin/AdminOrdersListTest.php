<?php

namespace Tests\Feature\Admin;

use App\Models\DriverLocation;
use App\Models\Order;
use App\Models\User;
use App\Queries\AdminOrdersQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Список заказов админки (пакет E): порядок по умолчанию, фильтры,
 * плашки-сводка, быстрые действия в строке и AJAX-фрагмент.
 */
class AdminOrdersListTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_default_list_puts_pending_first_regardless_of_age(): void
    {
        // Ожидание создано ПЕРВЫМ (меньший id): старая сортировка
        // «id desc» поставила бы завершённый заказ выше, приоритет
        // статуса обязан держать pending сверху.
        $pending = Order::factory()->create(['status' => Order::STATUS_PENDING]);
        $assigned = Order::factory()->create(['status' => Order::STATUS_ASSIGNED]);
        $completed = Order::factory()->create(['status' => Order::STATUS_COMPLETED]);

        $response = $this->actingAs($this->admin(), 'web')->get(route('order.index'));

        $response->assertOk();
        $response->assertSeeInOrder([
            '# '.$pending->id,
            '# '.$assigned->id,
            '# '.$completed->id,
        ]);
    }

    public function test_status_filter_narrows_the_list(): void
    {
        Order::factory()->create([
            'status' => Order::STATUS_PENDING,
            'client_last_name' => 'Пендингов',
        ]);
        Order::factory()->create([
            'status' => Order::STATUS_COMPLETED,
            'client_last_name' => 'Завершёнкин',
        ]);

        $response = $this->actingAs($this->admin(), 'web')
            ->get(route('order.index', ['status' => Order::STATUS_COMPLETED]));

        $response->assertOk();
        $response->assertSee('Завершёнкин');
        $response->assertDontSee('Пендингов');
    }

    public function test_driver_filter_and_search_narrow_the_list(): void
    {
        $driver = User::factory()->driver()->create();
        Order::factory()->create([
            'driver_id' => $driver->id,
            'client_last_name' => 'Сводителем',
        ]);
        Order::factory()->create([
            'driver_id' => null,
            'client_last_name' => 'Безводителя',
        ]);

        $byDriver = $this->actingAs($this->admin(), 'web')
            ->get(route('order.index', ['driver_id' => $driver->id]));
        $byDriver->assertSee('Сводителем');
        $byDriver->assertDontSee('Безводителя');

        $unassigned = $this->actingAs($this->admin(), 'web')
            ->get(route('order.index', ['driver_id' => 'none']));
        $unassigned->assertSee('Безводителя');
        $unassigned->assertDontSee('Сводителем');

        $bySearch = $this->actingAs($this->admin(), 'web')
            ->get(route('order.index', ['search' => 'Безводителя']));
        $bySearch->assertSee('Безводителя');
        $bySearch->assertDontSee('Сводителем');
    }

    public function test_inline_assign_sets_driver_offered_at_and_searching_status(): void
    {
        Event::fake();

        $driver = User::factory()->driver()->create();
        // Назначение теперь требует водителя на линии (защита
        // назначения, пакет 22.11.2026): свежая геопозиция.
        DriverLocation::create([
            'user_id' => $driver->id,
            'lat' => 53.5191,
            'lng' => 30.2415,
        ]);
        $order = Order::factory()->create([
            'status' => Order::STATUS_PENDING,
            'driver_id' => null,
            'driver_offered_at' => null,
        ]);

        $response = $this->actingAs($this->admin(), 'web')
            ->postJson(route('order.assign-driver', $order), ['driver_id' => $driver->id]);

        $response->assertOk()->assertJson(['ok' => true]);

        $order->refresh();
        $this->assertSame($driver->id, $order->driver_id);
        $this->assertNotNull($order->driver_offered_at);
        $this->assertSame(Order::STATUS_SEARCHING, $order->status);
    }

    public function test_inline_assign_can_unassign_driver(): void
    {
        Event::fake();

        $driver = User::factory()->driver()->create();
        $order = Order::factory()->create([
            'status' => Order::STATUS_SEARCHING,
            'driver_id' => $driver->id,
            'driver_offered_at' => now(),
        ]);

        $response = $this->actingAs($this->admin(), 'web')
            ->postJson(route('order.assign-driver', $order), ['driver_id' => '']);

        $response->assertOk()->assertJson(['ok' => true]);

        $order->refresh();
        $this->assertNull($order->driver_id);
        $this->assertNull($order->driver_offered_at);
    }

    public function test_inline_price_saves_and_validates(): void
    {
        $order = Order::factory()->create(['price' => null]);

        $response = $this->actingAs($this->admin(), 'web')
            ->postJson(route('order.update-price', $order), ['price' => 25.50]);

        $response->assertOk()->assertJson(['ok' => true]);
        $this->assertEqualsWithDelta(25.50, (float) $order->fresh()->price, 0.001);

        $this->actingAs($this->admin(), 'web')
            ->postJson(route('order.update-price', $order), ['price' => -1])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('price');
    }

    public function test_fragment_returns_partial_without_layout(): void
    {
        Order::factory()->create(['status' => Order::STATUS_PENDING]);

        $response = $this->actingAs($this->admin(), 'web')
            ->get(route('order.index', ['fragment' => 1]));

        $response->assertOk();
        // Внутри - плашки и таблица…
        $response->assertSee('data-status-chip="pending"', false);
        $response->assertSee('data-assign-select', false);
        // …а макета мастера (панель внимания, тосты) - нет.
        $response->assertDontSee('id="admin-alerts-panel"', false);
        $response->assertDontSee('id="admin-toasts"', false);
    }

    public function test_status_counts_cover_active_statuses_only(): void
    {
        Order::factory()->count(2)->create(['status' => Order::STATUS_PENDING]);
        Order::factory()->create(['status' => Order::STATUS_IN_PROGRESS]);
        Order::factory()->count(3)->create(['status' => Order::STATUS_COMPLETED]);
        Order::factory()->create(['status' => Order::STATUS_CANCELED]);

        $counts = AdminOrdersQuery::activeStatusCounts();

        $this->assertSame(2, $counts[Order::STATUS_PENDING]);
        $this->assertSame(1, $counts[Order::STATUS_IN_PROGRESS]);
        $this->assertSame(0, $counts[Order::STATUS_SEARCHING]);
        $this->assertArrayNotHasKey(Order::STATUS_COMPLETED, $counts);
        $this->assertArrayNotHasKey(Order::STATUS_CANCELED, $counts);
    }
}
