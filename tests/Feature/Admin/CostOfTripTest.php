<?php

namespace Tests\Feature\Admin;

use App\Models\CostOfTrip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CostOfTripTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_admin_can_save_decimal_prices_and_display_names(): void
    {
        CostOfTrip::create(['name' => 'City', 'price' => 8]);
        CostOfTrip::create(['name' => 'Outside_the_City', 'price' => 1.70]);

        $response = $this->actingAs($this->admin(), 'web')->put('/admin/cost-of-trip', [
            'price_city' => '10.50',
            'display_name_city' => 'Стандарт',
            'price_outside' => '2.25',
            'display_name_outside' => 'Премиум',
        ]);

        $response->assertRedirect(route('cost-of-trip.edit'));

        $city = CostOfTrip::where('name', 'City')->first();
        $this->assertSame('10.50', $city->price);
        $this->assertSame('Стандарт', $city->display_name);
        $this->assertSame('Стандарт', $city->label);

        $outside = CostOfTrip::where('name', 'Outside_the_City')->first();
        $this->assertSame('2.25', $outside->price);
        $this->assertSame('Премиум', $outside->label);
    }

    public function test_admin_cost_update_rejects_non_numeric_price(): void
    {
        CostOfTrip::create(['name' => 'City', 'price' => 8]);
        CostOfTrip::create(['name' => 'Outside_the_City', 'price' => 1.70]);

        $response = $this->actingAs($this->admin(), 'web')->put('/admin/cost-of-trip', [
            'price_city' => 'десять',
            'price_outside' => '2.25',
        ]);

        $response->assertSessionHasErrors('price_city');
        $this->assertSame('8.00', CostOfTrip::where('name', 'City')->first()->price);
    }

    public function test_label_falls_back_to_machine_name(): void
    {
        $tariff = CostOfTrip::create(['name' => 'City', 'price' => 8]);

        $this->assertSame('City', $tariff->label);
    }
}
