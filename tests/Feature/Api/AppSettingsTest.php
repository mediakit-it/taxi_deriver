<?php

namespace Tests\Feature\Api;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_app_settings_returns_branding_and_contacts(): void
    {
        Setting::set('company_name', 'СитиТакси');
        Setting::set('color_primary', '#112233');
        Setting::set('color_accent', '#445566');
        Setting::set('phones', json_encode([
            ['number' => '+375 29 111-11-11', 'label' => 'Диспетчерская'],
        ], JSON_UNESCAPED_UNICODE));

        $response = $this->getJson('/api/v1/app-settings');

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.company_name', 'СитиТакси');
        $response->assertJsonPath('data.colors.primary', '#112233');
        $response->assertJsonPath('data.colors.accent', '#445566');
        $response->assertJsonPath('data.phones.0.number', '+375 29 111-11-11');
        $response->assertJsonPath('data.phones.0.label', 'Диспетчерская');
    }

    public function test_app_settings_works_without_any_settings(): void
    {
        $response = $this->getJson('/api/v1/app-settings');

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.phones', []);
        $response->assertJsonPath('data.addresses', []);
    }

    public function test_app_settings_returns_driver_accept_seconds(): void
    {
        Setting::set('driver_accept_seconds', '45');

        $response = $this->getJson('/api/v1/app-settings');

        $response->assertOk();
        $response->assertJsonPath('data.driver_accept_seconds', 45);
    }
}
