<?php

namespace Tests\Feature\Api;

use App\Events\ClientOrderUpdated;
use App\Models\ClientNotification;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class OrderStatusSignalTest extends TestCase
{
    use RefreshDatabase;

    public function test_searching_status_sends_signal_without_notification_record(): void
    {
        Event::fake([ClientOrderUpdated::class]);
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'client_id' => $user->id,
            'status' => Order::STATUS_PENDING,
        ]);

        $order->update(['status' => Order::STATUS_SEARCHING]);

        Event::assertDispatched(
            ClientOrderUpdated::class,
            fn (ClientOrderUpdated $event) => $event->order->id === $order->id
                && $event->broadcastAs() === 'orderUpdated'
        );
        // "Searching" is an intermediate status: no feed entry by design.
        $this->assertSame(0, ClientNotification::count());
    }

    public function test_assigned_status_sends_signal_and_notification(): void
    {
        Event::fake([ClientOrderUpdated::class]);
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'client_id' => $user->id,
            'status' => Order::STATUS_PENDING,
        ]);

        $order->update(['status' => Order::STATUS_ASSIGNED]);

        Event::assertDispatched(ClientOrderUpdated::class);
        $this->assertSame(1, ClientNotification::count());
    }

    public function test_no_signal_without_status_change(): void
    {
        Event::fake([ClientOrderUpdated::class]);
        $order = Order::factory()->create(['status' => Order::STATUS_PENDING]);

        $order->update(['comment' => 'позвонить за 5 минут']);

        Event::assertNotDispatched(ClientOrderUpdated::class);
    }
}
