<?php

namespace Tests\Feature\Site;

use App\Models\CostOfTrip;
use App\Models\PolicyDocument;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_renders_demo_fallbacks_without_settings(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('СитиТакси');
        $response->assertSee('7777');
        $response->assertSee('zakaz@example.com');
        $response->assertSee('Ваш город');
    }

    public function test_landing_uses_company_name_from_settings(): void
    {
        Setting::set('company_name', 'ТестТакси');

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('ТестТакси');
        $response->assertSee('<title>ТестТакси', false);
    }

    public function test_landing_uses_phone_from_settings(): void
    {
        Setting::set('phones', json_encode([
            ['number' => '+375 29 111-11-11', 'label' => 'Диспетчерская'],
        ], JSON_UNESCAPED_UNICODE));

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('+375 29 111-11-11');
        $response->assertSee('Диспетчерская');
        $response->assertSee('tel:+375291111111', false);
    }

    public function test_landing_uses_email_and_city_from_settings(): void
    {
        Setting::set('notification_email', 'order@testtaxi.by');
        Setting::set('city', 'Быхов');

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('order@testtaxi.by');
        $response->assertSee('Быхов');
    }

    public function test_landing_renders_tariffs_from_database(): void
    {
        CostOfTrip::create(['name' => 'City', 'price' => '9']);
        CostOfTrip::create(['name' => 'Outside_the_City', 'price' => '2,00']);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('По городу');
        $response->assertSee('За городом');
        $response->assertSee('2,00');
        // Демо-карточка «Трансфер» из запасного блока не показывается
        $response->assertDontSee('25 <span class="pricing-unit">руб.</span>', false);
    }

    public function test_landing_lists_policy_documents_in_footer(): void
    {
        PolicyDocument::create([
            'title' => 'Правила перевозок',
            'file_path' => 'settings/documents/rules.pdf',
            'sort_order' => 1,
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Правила перевозок');
        $response->assertSee('storage/settings/documents/rules.pdf', false);
    }
}
