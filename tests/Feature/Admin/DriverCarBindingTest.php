<?php

namespace Tests\Feature\Admin;

use App\Models\Car;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DriverCarBindingTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    private function userPayload(User $driver, array $overrides = []): array
    {
        return array_merge([
            'last_name' => $driver->last_name,
            'first_name' => $driver->first_name,
            'email' => $driver->email,
            'phone' => $driver->phone,
            'role' => 'driving',
        ], $overrides);
    }

    public function test_admin_sets_driver_car_from_user_edit(): void
    {
        $driver = User::factory()->driver()->create();
        $car = Car::factory()->create();

        $response = $this->actingAs($this->admin(), 'web')
            ->put(route('user.update', $driver), $this->userPayload($driver, [
                'current_car_id' => $car->id,
            ]));

        $response->assertRedirect(route('user.index'));
        $this->assertSame($car->id, $driver->fresh()->current_car_id);
        $this->assertTrue($car->fresh()->boundDriver->is($driver));
    }

    public function test_admin_clears_driver_car_from_user_edit(): void
    {
        $car = Car::factory()->create();
        $driver = User::factory()->driver()->create(['current_car_id' => $car->id]);

        $response = $this->actingAs($this->admin(), 'web')
            ->put(route('user.update', $driver), $this->userPayload($driver, [
                'current_car_id' => '',
            ]));

        $response->assertRedirect(route('user.index'));
        $this->assertNull($driver->fresh()->current_car_id);
    }

    public function test_assigning_car_to_another_driver_clears_previous_binding(): void
    {
        $car = Car::factory()->create();
        $first = User::factory()->driver()->create(['current_car_id' => $car->id]);
        $second = User::factory()->driver()->create();

        $this->actingAs($this->admin(), 'web')
            ->put(route('user.update', $second), $this->userPayload($second, [
                'current_car_id' => $car->id,
            ]))
            ->assertRedirect(route('user.index'));

        $this->assertNull($first->fresh()->current_car_id);
        $this->assertSame($car->id, $second->fresh()->current_car_id);
    }

    public function test_car_list_shows_bound_driver(): void
    {
        $car = Car::factory()->create();
        User::factory()->driver()->create([
            'last_name' => 'Привязанный',
            'current_car_id' => $car->id,
        ]);

        $response = $this->actingAs($this->admin(), 'web')->get(route('car.index'));

        $response->assertOk();
        $response->assertSee('Привязанный');
    }

    public function test_car_edit_driver_selector_binds_and_releases(): void
    {
        $car = Car::factory()->create();
        $driver = User::factory()->driver()->create();

        $payload = [
            'brand' => $car->brand,
            'model' => $car->model,
            'number_plate' => $car->number_plate,
        ];

        $this->actingAs($this->admin(), 'web')
            ->put(route('car.update', $car), $payload + ['driver_id' => $driver->id])
            ->assertRedirect(route('car.index'));
        $this->assertSame($car->id, $driver->fresh()->current_car_id);

        $this->actingAs($this->admin(), 'web')
            ->put(route('car.update', $car), $payload + ['driver_id' => ''])
            ->assertRedirect(route('car.index'));
        $this->assertNull($driver->fresh()->current_car_id);
    }

    public function test_car_photo_upload_stores_file_and_path(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->admin(), 'web')
            ->post(route('car.store'), [
                'brand' => 'Skoda',
                'model' => 'Octavia',
                'number_plate' => '1234 AB-6',
                'photo' => UploadedFile::fake()->image('car.jpg'),
            ]);

        $response->assertRedirect(route('car.index'));

        $car = Car::first();
        $this->assertNotNull($car->photo);
        Storage::disk('public')->assertExists($car->photo);
    }

    public function test_car_photo_replace_deletes_old_file(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin(), 'web')
            ->post(route('car.store'), [
                'brand' => 'Skoda',
                'model' => 'Octavia',
                'number_plate' => '1234 AB-6',
                'photo' => UploadedFile::fake()->image('old.jpg'),
            ]);

        $car = Car::first();
        $oldPhoto = $car->photo;

        $this->actingAs($this->admin(), 'web')
            ->put(route('car.update', $car), [
                'brand' => 'Skoda',
                'model' => 'Octavia',
                'number_plate' => '1234 AB-6',
                'photo' => UploadedFile::fake()->image('new.jpg'),
            ])
            ->assertRedirect(route('car.index'));

        $car->refresh();
        $this->assertNotSame($oldPhoto, $car->photo);
        Storage::disk('public')->assertMissing($oldPhoto);
        Storage::disk('public')->assertExists($car->photo);
    }
}
