<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRoleAssignmentTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    private function userPayload(array $overrides = []): array
    {
        return array_merge([
            'last_name' => 'Тестов',
            'first_name' => 'Тест',
            'email' => 'new-user@example.test',
            'phone' => '+375 29 000-00-00',
            'password' => 'secret123',
            'role' => 'dispatcher',
        ], $overrides);
    }

    public function test_admin_can_create_dispatcher(): void
    {
        $response = $this->actingAs($this->admin(), 'web')
            ->post(route('user.store'), $this->userPayload());

        $response->assertRedirect(route('user.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'new-user@example.test',
            'role' => 'dispatcher',
        ]);
    }

    public function test_superadmin_role_cannot_be_assigned_on_create(): void
    {
        $response = $this->actingAs($this->admin(), 'web')
            ->post(route('user.store'), $this->userPayload(['role' => 'superadmin']));

        $response->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['email' => 'new-user@example.test']);
    }

    public function test_superadmin_role_cannot_be_assigned_on_update(): void
    {
        $target = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($this->admin(), 'web')
            ->put(route('user.update', $target), $this->userPayload([
                'email' => $target->email,
                'role' => 'superadmin',
            ]));

        $response->assertSessionHasErrors('role');
        $this->assertSame('user', $target->fresh()->role);
    }

    public function test_user_form_offers_dispatcher_but_not_superadmin(): void
    {
        $response = $this->actingAs($this->admin(), 'web')->get(route('user.create'));

        $response->assertOk();
        $response->assertSee('value="dispatcher"', false);
        $response->assertSee('Диспетчер');
        $response->assertDontSee('value="superadmin"', false);
    }

    public function test_superadmin_is_hidden_from_admin_user_list(): void
    {
        User::factory()->create(['role' => 'superadmin', 'email' => 'boss@example.test']);

        $response = $this->actingAs($this->admin(), 'web')->get(route('user.index'));

        $response->assertOk();
        $response->assertDontSee('boss@example.test');
    }

    public function test_superadmin_sees_superadmin_in_user_list(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin', 'email' => 'boss@example.test']);

        $response = $this->actingAs($superadmin, 'web')->get(route('user.index'));

        $response->assertOk();
        $response->assertSee('boss@example.test');
    }
}
