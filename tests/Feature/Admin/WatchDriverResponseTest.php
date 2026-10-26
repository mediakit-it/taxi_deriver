<?php

namespace Tests\Feature\Admin;

use App\Events\AdminAlert;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class WatchDriverResponseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::set('driver_accept_seconds', '60');
        Event::fake([AdminAlert::class]);
    }

    /**
     * Заказ в поиске с назначенным водителем, предложен 120 секунд назад
     * (таймаут в настройках - 60 секунд, то есть ответ уже просрочен).
     */
    private function searchingOrder(array $attributes = []): Order
    {
        return Order::factory()->searching()->create(array_merge([
            'driver_id' => User::factory()->driver()->create()->id,
            'driver_offered_at' => now()->subSeconds(120),
        ], $attributes));
    }

    private function assertNoResponseAlertNotDispatched(): void
    {
        Event::assertNotDispatched(
            AdminAlert::class,
            fn (AdminAlert $event) => $event->type === AdminAlert::TYPE_DRIVER_NO_RESPONSE
        );
    }

    public function test_overdue_order_alerts_dispatchers(): void
    {
        $order = $this->searchingOrder();

        Artisan::call('orders:watch-driver-response');

        Event::assertDispatched(
            AdminAlert::class,
            fn (AdminAlert $event) => $event->type === AdminAlert::TYPE_DRIVER_NO_RESPONSE
                && $event->orderId === $order->id
                && $event->waitingSeconds >= 120
        );
    }

    public function test_order_inside_timeout_is_not_alerted(): void
    {
        $this->searchingOrder(['driver_offered_at' => now()->subSeconds(10)]);

        Artisan::call('orders:watch-driver-response');

        $this->assertNoResponseAlertNotDispatched();
    }

    public function test_responded_driver_is_not_alerted(): void
    {
        // Водитель принял заказ: статус ушёл из searching.
        $this->searchingOrder(['status' => Order::STATUS_ASSIGNED]);

        Artisan::call('orders:watch-driver-response');

        $this->assertNoResponseAlertNotDispatched();
    }

    public function test_searching_order_without_driver_is_not_alerted(): void
    {
        Order::factory()->searching()->create([
            'driver_id' => null,
            'driver_offered_at' => now()->subSeconds(120),
        ]);

        Artisan::call('orders:watch-driver-response');

        $this->assertNoResponseAlertNotDispatched();
    }

    public function test_canceled_order_is_not_alerted(): void
    {
        $this->searchingOrder(['status' => Order::STATUS_CANCELED]);

        Artisan::call('orders:watch-driver-response');

        $this->assertNoResponseAlertNotDispatched();
    }
}
