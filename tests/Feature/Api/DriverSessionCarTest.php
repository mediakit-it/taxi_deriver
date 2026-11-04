<?php

namespace Tests\Feature\Api;

use App\Models\Car;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Привязка авто на смену в API: вход водителя с car_id, выход
// водителя и доклад о привязке (car-binding/report) обязаны держать
// единоличное владение - одно авто не числится за двумя водителями.
class DriverSessionCarTest extends TestCase
{
    use RefreshDatabase;

    private function authHeaders(User $user): array
    {
        $token = auth('api')->login($user);

        return ['Authorization' => 'Bearer ' . $token];
    }

    public function test_driver_login_with_car_id_binds_car_and_steals_it_from_previous_driver(): void
    {
        $car = Car::factory()->create(['number_plate' => '1111 AA-6']);
        $previous = User::factory()->driver()->create(['current_car_id' => $car->id]);
        User::factory()->driver()->create([
            'email' => 'shift-driver@example.test',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'shift-driver@example.test',
            'password' => 'password',
            'user_type' => 'driving',
            'car_id' => $car->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('user.current_car.id', $car->id);

        $driver = User::where('email', 'shift-driver@example.test')->first();
        $this->assertSame($car->id, $driver->current_car_id);
        $this->assertNull($previous->refresh()->current_car_id);
    }

    public function test_driver_login_without_car_id_keeps_existing_binding(): void
    {
        $car = Car::factory()->create();
        User::factory()->driver()->create([
            'email' => 'bound-driver@example.test',
            'current_car_id' => $car->id,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'bound-driver@example.test',
            'password' => 'password',
            'user_type' => 'driving',
        ]);

        $response->assertOk();

        $driver = User::where('email', 'bound-driver@example.test')->first();
        $this->assertSame($car->id, $driver->current_car_id);
    }

    public function test_client_login_ignores_car_id(): void
    {
        $car = Car::factory()->create();
        $owner = User::factory()->driver()->create(['current_car_id' => $car->id]);
        User::factory()->create([
            'email' => 'plain-client@example.test',
            'role' => 'user',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'plain-client@example.test',
            'password' => 'password',
            'user_type' => 'user',
            'car_id' => $car->id,
        ]);

        $response->assertOk();

        $client = User::where('email', 'plain-client@example.test')->first();
        $this->assertNull($client->current_car_id);
        // Чужая привязка от клиентского входа не пострадала
        $this->assertSame($car->id, $owner->refresh()->current_car_id);
    }

    public function test_driver_logout_clears_current_car(): void
    {
        $car = Car::factory()->create();
        $driver = User::factory()->driver()->create(['current_car_id' => $car->id]);

        $response = $this->postJson('/api/auth/logout', [], $this->authHeaders($driver));

        $response->assertOk();
        $this->assertNull($driver->refresh()->current_car_id);
    }

    public function test_car_binding_report_steals_car_from_previous_driver(): void
    {
        $car = Car::factory()->create();
        $previous = User::factory()->driver()->create(['current_car_id' => $car->id]);
        $driver = User::factory()->driver()->create();

        $response = $this->postJson('/api/v1/car-binding/report', [
            'car_id' => $car->id,
        ], $this->authHeaders($driver));

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSame($car->id, $driver->refresh()->current_car_id);
        $this->assertNull($previous->refresh()->current_car_id);
    }
}
