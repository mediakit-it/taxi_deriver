<?php

namespace Tests\Feature\Admin;

use App\Models\CostOfTrip;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class OrderTariffTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    private function seedTariffs(): void
    {
        CostOfTrip::create(['name' => 'City', 'price' => 8, 'display_name' => 'По городу']);
        CostOfTrip::create(['name' => 'Outside_the_City', 'price' => 1.70, 'display_name' => 'За городом']);
    }

    public function test_order_create_form_offers_tariff_classes(): void
    {
        $this->seedTariffs();

        $response = $this->actingAs($this->admin(), 'web')->get(route('order.create'));

        $response->assertOk();
        $response->assertSee('name="tariff"', false);
        $response->assertSee('value="City"', false);
        $response->assertSee('По городу');
        $response->assertSee('За городом');
    }

    public function test_admin_can_create_order_with_tariff_class(): void
    {
        Event::fake();
        $this->seedTariffs();

        $response = $this->actingAs($this->admin(), 'web')->post(route('order.store'), [
            'client_first_name' => 'Иван',
            'client_last_name' => 'Иванов',
            'client_phone' => '+375 29 000-00-00',
            'from_address' => 'г. Быхов, ул. Ленина, 1',
            'to_address' => 'г. Быхов, ул. Советская, 2',
            'tariff' => 'City',
        ]);

        $response->assertRedirect(route('order.index'));
        $this->assertDatabaseHas('orders', ['tariff' => 'City']);

        $order = Order::first();
        $this->assertSame('По городу', $order->tariffClass->label);
    }

    public function test_admin_can_change_tariff_class_on_order_edit(): void
    {
        Event::fake();
        $this->seedTariffs();
        $order = Order::factory()->create(['tariff' => null]);

        $response = $this->actingAs($this->admin(), 'web')->put(route('order.update', $order), [
            'tariff' => 'Outside_the_City',
        ]);

        $response->assertRedirect(route('order.index'));
        $this->assertSame('Outside_the_City', $order->fresh()->tariff);
        $this->assertSame('За городом', $order->fresh()->tariffClass->label);
    }

    public function test_order_edit_form_preselects_current_tariff(): void
    {
        $this->seedTariffs();
        $order = Order::factory()->create(['tariff' => 'Outside_the_City']);

        $response = $this->actingAs($this->admin(), 'web')->get(route('order.edit', $order));

        $response->assertOk();
        $response->assertSee('value="Outside_the_City" selected', false);
    }

    public function test_order_edit_form_preselects_current_driver(): void
    {
        $driver = User::factory()->driver()->create();
        $order = Order::factory()->create(['driver_id' => $driver->id]);

        $response = $this->actingAs($this->admin(), 'web')->get(route('order.edit', $order));

        $response->assertOk();
        $response->assertSee('value="' . $driver->id . '" selected', false);
    }
}
