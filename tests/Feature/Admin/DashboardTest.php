<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_guest_is_redirected_from_dashboard(): void
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_open_dashboard_with_seeded_orders(): void
    {
        Order::factory()->count(3)->create();
        Order::factory()->completed()->create(['price' => 25.50]);

        $response = $this->actingAs($this->admin(), 'web')->get('/admin/dashboard');

        $response->assertOk();
        $response->assertSee('Заказы сегодня');
        $response->assertSee('Выручка сегодня');
        $response->assertSee('25,50');
        $response->assertSee('Последние заказы');
    }

    public function test_dashboard_renders_on_empty_database(): void
    {
        $response = $this->actingAs($this->admin(), 'web')->get('/admin/dashboard');

        $response->assertOk();
        $response->assertSee('Заказов пока нет');
    }
}
