<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebLoginRolesTest extends TestCase
{
    use RefreshDatabase;

    private function panelUser(string $role): User
    {
        // Пароль фабрики по умолчанию - 'password'
        return User::factory()->create(['role' => $role]);
    }

    public function test_client_role_cannot_login_to_web_panel(): void
    {
        $user = $this->panelUser('user');

        $response = $this->post(route('login_auth'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertSame(
            'Учётная запись не имеет доступа в панель управления',
            session('errors')->get('email')[0]
        );
        $this->assertGuest('web');
    }

    public function test_driver_role_cannot_login_to_web_panel(): void
    {
        $user = $this->panelUser('driving');

        $response = $this->post(route('login_auth'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertSame(
            'Учётная запись не имеет доступа в панель управления',
            session('errors')->get('email')[0]
        );
        $this->assertGuest('web');
    }

    public function test_dispatcher_can_login_to_web_panel(): void
    {
        $user = $this->panelUser('dispatcher');

        $response = $this->post(route('login_auth'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($user, 'web');
    }

    public function test_admin_can_login_to_web_panel(): void
    {
        $user = $this->panelUser('admin');

        $response = $this->post(route('login_auth'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($user, 'web');
    }

    public function test_wrong_password_gives_credentials_error(): void
    {
        $user = $this->panelUser('admin');

        $response = $this->post(route('login_auth'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertSame('Неверный email или пароль', session('errors')->get('email')[0]);
        $this->assertGuest('web');
    }
}
