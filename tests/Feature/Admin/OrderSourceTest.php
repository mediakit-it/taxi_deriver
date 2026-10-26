<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class OrderSourceTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    private function orderPayload(array $overrides = []): array
    {
        return array_merge([
            'client_first_name' => 'Иван',
            'client_last_name' => 'Иванов',
            'client_phone' => '+375 29 000-00-00',
            'from_address' => 'г. Быхов, ул. Ленина, 1',
            'to_address' => 'г. Быхов, ул. Советская, 2',
        ], $overrides);
    }

    public function test_admin_created_order_defaults_to_phone_source(): void
    {
        Event::fake();

        $response = $this->actingAs($this->admin(), 'web')
            ->post(route('order.store'), $this->orderPayload());

        $response->assertRedirect(route('order.index'));
        $this->assertSame('phone', Order::first()->source);
    }

    public function test_admin_created_order_accepts_explicit_app_source(): void
    {
        Event::fake();

        $this->actingAs($this->admin(), 'web')
            ->post(route('order.store'), $this->orderPayload(['source' => 'app']));

        $this->assertSame('app', Order::first()->source);
    }

    public function test_functional_allowlist_limits_admin_order_source(): void
    {
        Event::fake();
        Setting::set('functional_order_sources', 'app');

        // Источник не передан: подставляется единственный разрешённый
        $this->actingAs($this->admin(), 'web')
            ->post(route('order.store'), $this->orderPayload());
        $this->assertSame('app', Order::first()->source);

        // Запрещённый источник из формы не принимается
        $this->actingAs($this->admin(), 'web')
            ->post(route('order.store'), $this->orderPayload(['source' => 'phone']));
        $this->assertSame('app', Order::latest('id')->first()->source);
    }

    public function test_app_order_source_cannot_be_changed_in_admin(): void
    {
        Event::fake();
        $order = Order::factory()->create(['source' => 'app']);

        $this->actingAs($this->admin(), 'web')
            ->put(route('order.update', $order), ['source' => 'phone']);

        $this->assertSame('app', $order->fresh()->source);
    }

    public function test_orders_list_shows_source_label(): void
    {
        Order::factory()->create(['source' => 'phone']);
        Order::factory()->create(['source' => 'app']);

        $response = $this->actingAs($this->admin(), 'web')->get(route('order.index'));

        $response->assertOk();
        $response->assertSee('Источник');
        $response->assertSee('Телефон');
        $response->assertSee('Приложение');
    }
}
