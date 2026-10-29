<?php

namespace Tests\Feature\Api;

use App\Models\DriverLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverLocationUpdateTest extends TestCase
{
    use RefreshDatabase;

    private function authHeaders(User $user): array
    {
        $token = auth('api')->login($user);

        return ['Authorization' => 'Bearer ' . $token];
    }

    public function test_location_update_is_written_for_authenticated_driver_not_body_user_id(): void
    {
        $driver = User::factory()->create();

        // The app can send user_id=0 (its local profile is not saved yet
        // on a fresh install): the ping must still land on the driver
        // who owns the token, not on a nonexistent user 0.
        $response = $this->postJson('/api/location/update', [
            'user_id' => 0,
            'lat' => 53.5203,
            'lng' => 30.2371,
            'is_online' => true,
            'isZakaz' => false,
            'orderId' => null,
        ], $this->authHeaders($driver));

        $response->assertOk()->assertJson(['status' => 'success']);

        $location = DriverLocation::sole();
        $this->assertSame($driver->id, $location->user_id);
        $this->assertEqualsWithDelta(53.5203, (float) $location->lat, 0.0001);
        $this->assertEqualsWithDelta(30.2371, (float) $location->lng, 0.0001);
        $this->assertFalse($location->is_zakaz);
    }

    public function test_location_update_requires_authentication(): void
    {
        $response = $this->postJson('/api/location/update', [
            'user_id' => 1,
            'lat' => 53.5203,
            'lng' => 30.2371,
        ]);

        $response->assertUnauthorized();
        $this->assertSame(0, DriverLocation::count());
    }
}
