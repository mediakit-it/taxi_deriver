<?php

namespace Tests\Feature\Admin;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FunctionalSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function superadmin(): User
    {
        return User::factory()->create(['role' => 'superadmin']);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'functional_price_mode' => 'auto',
            'functional_assignment_mode' => 'combined',
            'functional_order_sources' => 'phone',
            'functional_preorders_enabled' => '1',
        ], $overrides);
    }

    public function test_superadmin_sees_functional_section_on_settings_page(): void
    {
        $response = $this->actingAs($this->superadmin(), 'web')->get('/admin/settings');

        $response->assertOk();
        $response->assertSee('Функционал');
        $response->assertSee('name="functional_price_mode"', false);
    }

    public function test_admin_does_not_see_functional_section(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin, 'web')->get('/admin/settings');

        $response->assertOk();
        $response->assertDontSee('functional_price_mode');
    }

    public function test_superadmin_can_save_functional_settings(): void
    {
        $response = $this->actingAs($this->superadmin(), 'web')
            ->put(route('settings.functional.update'), $this->payload());

        $response->assertRedirect(route('settings.edit'));
        $this->assertSame('auto', Setting::get('functional_price_mode'));
        $this->assertSame('combined', Setting::get('functional_assignment_mode'));
        $this->assertSame('phone', Setting::get('functional_order_sources'));
        $this->assertSame('1', Setting::get('functional_preorders_enabled'));
        // Чекбокс не отмечен - в запросе отсутствует, сохраняется 0
        $this->assertSame('0', Setting::get('functional_ratings_enabled'));
    }

    public function test_admin_cannot_save_functional_settings(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin, 'web')
            ->put(route('settings.functional.update'), $this->payload());

        $response->assertForbidden();
        $this->assertNull(Setting::get('functional_price_mode'));
    }

    public function test_functional_settings_validation_rejects_unknown_mode(): void
    {
        $response = $this->actingAs($this->superadmin(), 'web')
            ->put(route('settings.functional.update'), $this->payload([
                'functional_price_mode' => 'weird',
            ]));

        $response->assertSessionHasErrors('functional_price_mode');
    }
}
