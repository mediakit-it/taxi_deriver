<?php

namespace Tests\Feature\Api;

use App\Models\Car;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CarBindingReportTest extends TestCase
{
    use RefreshDatabase;

    private function authHeaders(User $user): array
    {
        $token = auth('api')->login($user);

        return ['Authorization' => 'Bearer ' . $token];
    }

    public function test_driver_reports_current_car(): void
    {
        $driver = User::factory()->driver()->create();
        $car = Car::factory()->create([
            'brand' => 'Skoda',
            'model' => 'Octavia',
            'number_plate' => '1234 AB-6',
        ]);

        $response = $this->postJson('/api/v1/car-binding/report', [
            'car_id' => $car->id,
        ], $this->authHeaders($driver));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.car_id', $car->id)
            ->assertJsonPath('data.label', 'Skoda Octavia')
            ->assertJsonPath('data.plate', '1234 AB-6');

        $driver->refresh();
        $this->assertSame($car->id, $driver->current_car_id);
        $this->assertTrue($driver->currentCar->is($car));
    }

    public function test_report_with_unknown_car_returns_422_and_keeps_current_car(): void
    {
        $driver = User::factory()->driver()->create();
        $car = Car::factory()->create();
        $driver->current_car_id = $car->id;
        $driver->save();

        $response = $this->postJson('/api/v1/car-binding/report', [
            'car_id' => $car->id + 1000,
        ], $this->authHeaders($driver));

        $response->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Автомобиль не найден');

        $this->assertSame($car->id, $driver->refresh()->current_car_id);
    }

    public function test_report_is_forbidden_for_non_driver_role(): void
    {
        $client = User::factory()->create(['role' => 'user']);
        $car = Car::factory()->create();

        $response = $this->postJson('/api/v1/car-binding/report', [
            'car_id' => $car->id,
        ], $this->authHeaders($client));

        $response->assertForbidden();
        $this->assertNull($client->refresh()->current_car_id);
    }

    public function test_report_requires_authentication(): void
    {
        $car = Car::factory()->create();

        $response = $this->postJson('/api/v1/car-binding/report', [
            'car_id' => $car->id,
        ]);

        $response->assertUnauthorized();
    }

    public function test_report_requires_integer_car_id(): void
    {
        $driver = User::factory()->driver()->create();

        $this->postJson('/api/v1/car-binding/report', [], $this->authHeaders($driver))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('car_id');

        $this->postJson('/api/v1/car-binding/report', [
            'car_id' => 'not-a-number',
        ], $this->authHeaders($driver))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('car_id');

        $this->assertNull($driver->refresh()->current_car_id);
    }
}
