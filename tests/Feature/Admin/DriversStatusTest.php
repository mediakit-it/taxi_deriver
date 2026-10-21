<?php

namespace Tests\Feature\Admin;

use App\Models\DriverLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriversStatusTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    private function driverWithPing(?string $pingedAt, bool $busy = false): User
    {
        $driver = User::factory()->driver()->create();

        if ($pingedAt !== null) {
            DriverLocation::factory()->create([
                'user_id' => $driver->id,
                'is_zakaz' => $busy,
                'updated_at' => $pingedAt,
            ]);
        }

        return $driver;
    }

    public function test_guest_is_redirected_from_drivers_page(): void
    {
        $response = $this->get('/admin/drivers');

        $response->assertRedirect(route('login'));
    }

    public function test_drivers_page_shows_all_three_statuses(): void
    {
        // На линии: пинг минуту назад
        $this->driverWithPing(now()->subMinute()->toDateTimeString());
        // Не на линии: пинг три часа назад
        $this->driverWithPing(now()->subHours(3)->toDateTimeString());
        // Не работает: пинг двое суток назад
        $this->driverWithPing(now()->subDays(2)->toDateTimeString());
        // Не работает: локации нет вообще
        $this->driverWithPing(null);

        $response = $this->actingAs($this->admin(), 'web')->get('/admin/drivers');

        $response->assertOk();
        $response->assertSee('На линии');
        $response->assertSee('Не на линии');
        $response->assertSee('Не работает');
    }

    public function test_online_driver_on_order_gets_busy_badge(): void
    {
        $this->driverWithPing(now()->toDateTimeString(), busy: true);

        $response = $this->actingAs($this->admin(), 'web')->get('/admin/drivers');

        $response->assertOk();
        $response->assertSee('На линии · на заказе');
    }

    public function test_work_status_derivation_on_model(): void
    {
        $online = new DriverLocation();
        $online->updated_at = now()->subMinutes(2);
        $this->assertSame(DriverLocation::STATUS_ONLINE, $online->work_status);

        $offline = new DriverLocation();
        $offline->updated_at = now()->subHours(2);
        $this->assertSame(DriverLocation::STATUS_OFFLINE, $offline->work_status);

        $stale = new DriverLocation();
        $stale->updated_at = now()->subDays(3);
        $this->assertSame(DriverLocation::STATUS_NOT_WORKING, $stale->work_status);
    }
}
