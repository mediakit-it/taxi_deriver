<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserBlockingTest extends TestCase
{
    use RefreshDatabase;

    private function authHeaders(User $user): array
    {
        $token = auth('api')->login($user);

        return ['Authorization' => 'Bearer ' . $token];
    }

    public function test_blocked_panel_user_cannot_log_in_via_web(): void
    {
        $admin = User::factory()->admin()->create(['is_blocked' => true]);

        $response = $this->post('/login_auth', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest('web');
    }

    public function test_blocked_user_cannot_log_in_via_api(): void
    {
        $user = User::factory()->create(['is_blocked' => true]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
            'user_type' => 'user',
        ]);

        $response->assertForbidden()
            ->assertJson(['status' => 'error', 'message' => 'Пользователь заблокирован']);
    }

    public function test_api_token_issued_before_block_stops_working(): void
    {
        $user = User::factory()->create();
        $headers = $this->authHeaders($user);

        // До блокировки токен работает в обеих аутентифицированных группах API
        $this->getJson('/api/orders', $headers)->assertOk();
        $this->getJson('/api/v1/notifications/unread-count', $headers)->assertOk();

        $user->is_blocked = true;
        $user->save();

        $this->getJson('/api/orders', $headers)
            ->assertForbidden()
            ->assertJson(['status' => 'error', 'message' => 'Пользователь заблокирован']);

        $this->getJson('/api/v1/notifications/unread-count', $headers)
            ->assertForbidden();
    }

    public function test_blocked_panel_user_loses_active_web_session(): void
    {
        $admin = User::factory()->admin()->create(['is_blocked' => true]);

        $response = $this->actingAs($admin, 'web')->get('/admin/dashboard');

        $response->assertRedirect('/');
        $this->assertGuest('web');
    }

    public function test_toggle_block_flips_flag_both_ways(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin, 'web')
            ->post(route('user.toggle-block', $user))
            ->assertSessionHas('success');
        $this->assertTrue($user->fresh()->is_blocked);

        $this->actingAs($admin, 'web')
            ->post(route('user.toggle-block', $user))
            ->assertSessionHas('success');
        $this->assertFalse($user->fresh()->is_blocked);
    }

    public function test_admin_cannot_block_own_account(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin, 'web')
            ->post(route('user.toggle-block', $admin))
            ->assertSessionHas('error');

        $this->assertFalse($admin->fresh()->is_blocked);
    }

    public function test_superadmin_cannot_be_blocked(): void
    {
        $admin = User::factory()->admin()->create();
        $superadmin = User::factory()->create(['role' => 'superadmin']);

        $this->actingAs($admin, 'web')
            ->post(route('user.toggle-block', $superadmin))
            ->assertSessionHas('error');

        $this->assertFalse($superadmin->fresh()->is_blocked);
    }
}
