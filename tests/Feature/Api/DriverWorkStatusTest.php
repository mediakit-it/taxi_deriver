<?php

namespace Tests\Feature\Api;

use App\Models\DriverLocation;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Уход водителя с линии (пакет №160): приложение шлёт в пинге
 * геопозиции флаг is_online, бэк его хранит, и статус работы
 * водителя (DriverLocation::work_status) при is_online=false -
 * сразу «не на линии», даже при свежем пинге. Назначение такого
 * водителя блокируется существующим гардом AssignOrderDriver.
 */
class DriverWorkStatusTest extends TestCase
{
    use RefreshDatabase;

    private function authHeaders(User $user): array
    {
        return ['Authorization' => 'Bearer '.auth('api')->login($user)];
    }

    private function ping(User $driver, array $payload): void
    {
        $this->postJson('/api/location/update', array_merge([
            'lat' => 53.5191,
            'lng' => 30.2415,
            'isZakaz' => false,
        ], $payload), $this->authHeaders($driver))
            ->assertOk()
            ->assertJsonPath('status', 'success');
    }

    public function test_ping_with_is_online_false_makes_driver_offline(): void
    {
        Event::fake();
        $driver = User::factory()->driver()->create();

        $this->ping($driver, ['is_online' => false]);

        $location = DriverLocation::where('user_id', $driver->id)->firstOrFail();
        $this->assertFalse($location->is_online);
        // Пинг только что пришёл (updated_at свежий), но статус -
        // «не на линии»: явный флаг важнее свежести.
        $this->assertSame(DriverLocation::STATUS_OFFLINE, $location->work_status);
    }

    public function test_ping_with_is_online_true_makes_driver_online(): void
    {
        Event::fake();
        $driver = User::factory()->driver()->create();

        $this->ping($driver, ['is_online' => false]);
        $this->ping($driver, ['is_online' => true]);

        $location = DriverLocation::where('user_id', $driver->id)->firstOrFail();
        $this->assertTrue($location->is_online);
        $this->assertSame(DriverLocation::STATUS_ONLINE, $location->work_status);
    }

    public function test_ping_without_is_online_keeps_previous_flag(): void
    {
        Event::fake();
        $driver = User::factory()->driver()->create();

        $this->ping($driver, ['is_online' => false]);
        // Пинг старой версии приложения - без флага: колонку не
        // трогаем, статус водителя не сбрасывается.
        $this->ping($driver, []);

        $location = DriverLocation::where('user_id', $driver->id)->firstOrFail();
        $this->assertFalse($location->is_online);
        $this->assertSame(DriverLocation::STATUS_OFFLINE, $location->work_status);
    }

    public function test_offline_by_flag_driver_cannot_be_assigned(): void
    {
        Event::fake();
        $admin = User::factory()->admin()->create();
        $driver = User::factory()->driver()->create();
        // Свежая геопозиция есть, но водитель ушёл с линии флагом.
        $this->ping($driver, ['is_online' => false]);
        $order = Order::factory()->create([
            'status' => Order::STATUS_PENDING,
            'driver_id' => null,
        ]);

        $response = $this->actingAs($admin, 'web')
            ->postJson(route('order.assign-driver', $order), ['driver_id' => $driver->id]);

        $response->assertStatus(422)
            ->assertJsonPath('ok', false);

        $this->assertNull($order->fresh()->driver_id);
    }
}
