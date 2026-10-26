<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class DispatcherAccessTest extends TestCase
{
    use RefreshDatabase;

    private function dispatcher(): User
    {
        return User::factory()->create(['role' => 'dispatcher']);
    }

    public function test_dispatcher_can_open_working_pages(): void
    {
        $dispatcher = $this->dispatcher();

        foreach ([
            '/admin/dashboard',
            '/admin/order',
            '/admin/drivers',
            '/admin/driver-locations',
            '/admin/messenger',
            '/admin/car',
        ] as $url) {
            $this->actingAs($dispatcher, 'web')->get($url)->assertOk();
        }
    }

    public function test_dispatcher_cannot_open_management_pages(): void
    {
        $dispatcher = $this->dispatcher();

        foreach ([
            '/admin/settings',
            '/admin/user',
            '/admin/user/create',
            '/admin/cost-of-trip',
            '/admin/car/create',
        ] as $url) {
            $this->actingAs($dispatcher, 'web')->get($url)->assertForbidden();
        }
    }

    public function test_dispatcher_cannot_write_management_routes(): void
    {
        $dispatcher = $this->dispatcher();

        $this->actingAs($dispatcher, 'web')
            ->post('/admin/settings/documents', ['title' => 'Документ'])
            ->assertForbidden();

        $this->actingAs($dispatcher, 'web')
            ->put('/admin/settings/functional', [
                'functional_price_mode' => 'auto',
                'functional_assignment_mode' => 'auto',
                'functional_order_sources' => 'both',
            ])
            ->assertForbidden();
    }

    public function test_dispatcher_can_create_order(): void
    {
        Event::fake();
        $dispatcher = $this->dispatcher();

        $response = $this->actingAs($dispatcher, 'web')->post(route('order.store'), [
            'client_first_name' => 'Иван',
            'client_last_name' => 'Иванов',
            'client_phone' => '+375 29 000-00-00',
            'from_address' => 'г. Быхов, ул. Ленина, 1',
            'to_address' => 'г. Быхов, ул. Советская, 2',
        ]);

        $response->assertRedirect(route('order.index'));
        $this->assertDatabaseHas('orders', ['client_phone' => '+375 29 000-00-00']);
    }

    public function test_dispatcher_sidebar_hides_management_items(): void
    {
        $response = $this->actingAs($this->dispatcher(), 'web')->get('/admin/order');

        $response->assertOk();
        $response->assertDontSee('Пользователи');
        $response->assertDontSee('Стоимость поездок');
        $response->assertDontSee('Настройки');
        $response->assertSee('Заказы');
        $response->assertSee('Водители на карте');
    }
}
