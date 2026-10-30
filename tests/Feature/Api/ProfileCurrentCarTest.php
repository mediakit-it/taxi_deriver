<?php

namespace Tests\Feature\Api;

use App\Models\Car;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileCurrentCarTest extends TestCase
{
    use RefreshDatabase;

    private function authHeaders(User $user): array
    {
        $token = auth('api')->login($user);

        return ['Authorization' => 'Bearer ' . $token];
    }

    public function test_me_returns_bound_car_as_current_car(): void
    {
        $car = Car::factory()->create([
            'brand' => 'Skoda',
            'model' => 'Octavia',
            'number_plate' => '1234 AB-6',
        ]);
        $driver = User::factory()->driver()->create(['current_car_id' => $car->id]);

        $response = $this->getJson('/api/auth/me', $this->authHeaders($driver));

        $response->assertOk()
            ->assertJsonPath('current_car.id', $car->id)
            ->assertJsonPath('current_car.brand', 'Skoda')
            ->assertJsonPath('current_car.model', 'Octavia')
            ->assertJsonPath('current_car.number_plate', '1234 AB-6')
            // Исторический ключ car в ответе сохраняется как был
            ->assertJsonStructure(['car']);
    }

    public function test_me_returns_null_current_car_when_unbound(): void
    {
        $driver = User::factory()->driver()->create();

        $response = $this->getJson('/api/auth/me', $this->authHeaders($driver));

        $response->assertOk()
            ->assertJsonPath('current_car', null);
    }

    public function test_login_returns_current_car_in_user_payload(): void
    {
        $car = Car::factory()->create(['number_plate' => '7777 EK-6']);
        User::factory()->driver()->create([
            'email' => 'driver-login@example.test',
            'current_car_id' => $car->id,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'driver-login@example.test',
            'password' => 'password',
            'user_type' => 'driving',
        ]);

        $response->assertOk()
            ->assertJsonPath('user.current_car.id', $car->id)
            ->assertJsonPath('user.current_car.number_plate', '7777 EK-6');
    }
}
