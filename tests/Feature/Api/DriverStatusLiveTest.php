<?php

namespace Tests\Feature\Api;

use App\Events\DriverWorkStatusChanged;
use App\Models\DriverLocation;
use App\Models\DriverStatusPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Живой статус водителя в админке (пакет №162): переход флага
 * is_online в пинге геопозиции открывает период работы в новом
 * статусе и шлёт событие DriverWorkStatusChanged; повторный пинг
 * с тем же флагом - не переход: ни нового периода, ни события.
 * Выдача карты отдаёт флаг is_online и начало текущего периода.
 */
class DriverStatusLiveTest extends TestCase
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

    public function test_ping_offline_opens_period_and_broadcasts_once(): void
    {
        Event::fake();
        $driver = User::factory()->driver()->create();

        $this->ping($driver, ['is_online' => false]);

        $periods = DriverStatusPeriod::where('user_id', $driver->id)->get();
        $this->assertCount(1, $periods);
        $this->assertSame(DriverStatusPeriod::STATUS_OFFLINE, $periods->first()->status);
        $this->assertNull($periods->first()->ended_at);
        Event::assertDispatched(DriverWorkStatusChanged::class, function (DriverWorkStatusChanged $event) use ($driver) {
            return $event->userId === $driver->id
                && $event->workStatus === DriverLocation::STATUS_OFFLINE
                && $event->statusSince !== null;
        });

        // Повторный пинг с тем же флагом - не переход: период
        // остаётся один, событие второй раз не уходит.
        $this->ping($driver, ['is_online' => false]);

        $this->assertSame(1, DriverStatusPeriod::where('user_id', $driver->id)->count());
        Event::assertDispatchedTimes(DriverWorkStatusChanged::class, 1);
    }

    public function test_ping_online_after_offline_closes_offline_and_opens_online(): void
    {
        Event::fake();
        $driver = User::factory()->driver()->create();

        $this->ping($driver, ['is_online' => false]);
        $this->ping($driver, ['is_online' => true]);

        $periods = DriverStatusPeriod::where('user_id', $driver->id)->orderBy('id')->get();
        $this->assertCount(2, $periods);
        $this->assertSame(DriverStatusPeriod::STATUS_OFFLINE, $periods[0]->status);
        $this->assertNotNull($periods[0]->ended_at);
        $this->assertSame(DriverStatusPeriod::STATUS_ONLINE, $periods[1]->status);
        $this->assertNull($periods[1]->ended_at);
    }

    public function test_driver_locations_endpoint_includes_is_online_and_status_since(): void
    {
        Event::fake();
        $admin = User::factory()->admin()->create();
        $driver = User::factory()->driver()->create();

        $this->ping($driver, ['is_online' => false]);

        $response = $this->actingAs($admin, 'web')
            ->getJson('/admin/data/driver-locations')
            ->assertOk()
            ->assertJsonPath('data.0.is_online', false)
            ->assertJsonPath('data.0.work_status', 'offline');

        $this->assertNotNull($response->json('data.0.status_since'));
    }
}
