<?php

namespace Tests\Feature\Api;

use App\Models\Car;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CarBindingTest extends TestCase
{
    use RefreshDatabase;

    public function test_binding_is_forbidden_when_password_is_not_configured(): void
    {
        $response = $this->postJson('/api/v1/car-binding/cars', [
            'password' => 'whatever',
        ]);

        $response->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Привязка не настроена');
    }

    public function test_binding_is_forbidden_on_wrong_password(): void
    {
        Setting::set('car_bind_password', Hash::make('installer-secret'));

        $response = $this->postJson('/api/v1/car-binding/cars', [
            'password' => 'wrong-password',
        ]);

        $response->assertForbidden()
            ->assertJsonPath('success', false);
    }

    public function test_binding_returns_car_list_on_correct_password(): void
    {
        Setting::set('car_bind_password', Hash::make('installer-secret'));

        $skoda = Car::factory()->create([
            'brand' => 'Skoda',
            'model' => 'Octavia',
            'number_plate' => '1234 AB-6',
        ]);
        $kia = Car::factory()->create([
            'brand' => 'Kia',
            'model' => 'Rio',
            'number_plate' => '5678 AE-6',
        ]);

        $response = $this->postJson('/api/v1/car-binding/cars', [
            'password' => 'installer-secret',
        ]);

        // Список отсортирован по марке: Kia раньше Skoda.
        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $kia->id)
            ->assertJsonPath('data.0.label', 'Kia Rio')
            ->assertJsonPath('data.0.plate', '5678 AE-6')
            ->assertJsonPath('data.1.id', $skoda->id)
            ->assertJsonPath('data.1.label', 'Skoda Octavia')
            ->assertJsonPath('data.1.plate', '1234 AB-6');
    }

    public function test_binding_requires_a_password(): void
    {
        Setting::set('car_bind_password', Hash::make('installer-secret'));

        $response = $this->postJson('/api/v1/car-binding/cars', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('password');
    }

    public function test_app_settings_does_not_expose_car_bind_password(): void
    {
        $hash = Hash::make('installer-secret');
        Setting::set('car_bind_password', $hash);

        $response = $this->getJson('/api/v1/app-settings');

        $response->assertOk();
        $response->assertJsonMissingPath('data.car_bind_password');
        $this->assertStringNotContainsString($hash, $response->getContent());
        $this->assertStringNotContainsString('car_bind_password', $response->getContent());
    }
}
