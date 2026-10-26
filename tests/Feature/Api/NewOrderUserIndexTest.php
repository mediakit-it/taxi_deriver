<?php

namespace Tests\Feature\Api;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class NewOrderUserIndexTest extends TestCase
{
    use RefreshDatabase;

    private function authHeaders(User $user): array
    {
        $token = auth('api')->login($user);

        return ['Authorization' => 'Bearer ' . $token];
    }

    /**
     * Ids the «Новые заказы» endpoint returns for the given driver.
     *
     * @return array<int, int>
     */
    private function listedOrderIds(User $driver): array
    {
        $response = $this->getJson('/api/newOrderUserIndex', $this->authHeaders($driver));

        $response->assertOk();

        return collect($response->json('data'))->pluck('id')->all();
    }

    public function test_accepted_and_active_orders_do_not_appear_in_new_orders(): void
    {
        Event::fake();
        $driver = User::factory()->driver()->create();
        $assigned = Order::factory()->create([
            'status' => Order::STATUS_ASSIGNED,
            'driver_id' => $driver->id,
        ]);
        $onPlace = Order::factory()->create([
            'status' => Order::STATUS_ON_PLACE,
            'driver_id' => $driver->id,
        ]);
        $inProgress = Order::factory()->create([
            'status' => Order::STATUS_IN_PROGRESS,
            'driver_id' => $driver->id,
        ]);

        $ids = $this->listedOrderIds($driver);

        // Принятый заказ уходит из «Новых заказов» в активные (GET /api/orders),
        // а не остаётся висеть в списке новых после перезагрузки.
        $this->assertNotContains($assigned->id, $ids);
        $this->assertNotContains($onPlace->id, $ids);
        $this->assertNotContains($inProgress->id, $ids);
    }

    public function test_own_searching_offer_still_appears_in_new_orders(): void
    {
        Event::fake();
        $driver = User::factory()->driver()->create();
        $offer = Order::factory()->create([
            'status' => Order::STATUS_SEARCHING,
            'driver_id' => $driver->id,
            'driver_offered_at' => now()->subSeconds(30),
        ]);
        // Персональное предложение другому водителю в мой список не попадает.
        $otherDriver = User::factory()->driver()->create();
        $foreignOffer = Order::factory()->create([
            'status' => Order::STATUS_SEARCHING,
            'driver_id' => $otherDriver->id,
            'driver_offered_at' => now()->subSeconds(30),
        ]);

        $ids = $this->listedOrderIds($driver);

        $this->assertContains($offer->id, $ids);
        $this->assertNotContains($foreignOffer->id, $ids);
    }

    public function test_unassigned_pending_order_still_appears_in_new_orders(): void
    {
        Event::fake();
        $driver = User::factory()->driver()->create();
        $pending = Order::factory()->create([
            'status' => Order::STATUS_PENDING,
            'driver_id' => null,
        ]);

        $ids = $this->listedOrderIds($driver);

        $this->assertContains($pending->id, $ids);
    }
}
