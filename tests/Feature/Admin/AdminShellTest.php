<?php

namespace Tests\Feature\Admin;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminShellTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_shows_company_wordmark_instead_of_logo_image(): void
    {
        Setting::set('company_name', 'Тестовое Такси');

        $response = $this->get('/authorization');

        $response->assertOk();
        $response->assertSee('ТЕСТОВОЕ');
        $response->assertSee('brand-wordmark__box', false);

        // На странице входа нет картинки-логотипа - там текстовый вордмарк
        $response->assertSee('ТАКСИ');
        $this->assertStringNotContainsString('logo1.png', $response->getContent());
    }

    public function test_sidebar_user_block_at_bottom_shows_name_initials_and_logout(): void
    {
        $admin = User::factory()->admin()->create([
            'first_name' => 'Анна',
            'last_name' => 'Борисова',
        ]);

        $response = $this->actingAs($admin, 'web')->get('/admin/dashboard');

        $response->assertOk();
        $response->assertSee('Анна Борисова');
        $response->assertSee('АБ');
        $response->assertSee('Выйти');

        // Блок пользователя стоит после последнего пункта меню («Настройки»)
        $content = $response->getContent();
        $this->assertGreaterThan(
            mb_strpos($content, 'Настройки'),
            mb_strpos($content, 'Анна Борисова')
        );
    }

    public function test_user_can_logout_via_post(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin, 'web')->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest('web');
    }
}
