<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Services\YandexGeocodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Подсказки адресов для панели (GET /admin/data/geocode-search):
// веб-обёртка над YandexGeocodeService для форм админки - мобильный
// API-роут геокодера закрыт JWT и браузеру панели недоступен.
class AdminGeocodeSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin/data/geocode-search?address='.urlencode('Ленина'))
            ->assertRedirect('/authorization');
    }

    public function test_admin_gets_service_points_in_api_list_shape(): void
    {
        $points = [[
            'lat' => 53.5203,
            'lng' => 30.2371,
            'name' => 'улица Ленина, 10',
            'address' => 'Беларусь, Могилёвская область, Быхов, улица Ленина, 10',
            'short_address' => 'улица Ленина, 10, Быхов',
            'display_address' => 'Быхов, улица Ленина, 10',
            'locality' => 'Быхов',
        ]];

        $fake = new class($points) extends YandexGeocodeService
        {
            public function __construct(private readonly array $points)
            {
            }

            public function isConfigured(): bool
            {
                return true;
            }

            public function geocodeMany(string $address, int $limit): array
            {
                return $this->points;
            }
        };

        $this->app->instance(YandexGeocodeService::class, $fake);

        $admin = User::factory()->admin()->create();

        $this->actingAs($admin, 'web')
            ->getJson('/admin/data/geocode-search?address='.urlencode('Быхов, Ленина').'&limit=5')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.lat', 53.5203)
            ->assertJsonPath('data.0.lng', 30.2371)
            ->assertJsonPath('data.0.address', 'Беларусь, Могилёвская область, Быхов, улица Ленина, 10')
            ->assertJsonPath('data.0.display_address', 'Быхов, улица Ленина, 10')
            ->assertJsonPath('data.0.locality', 'Быхов');
    }

    public function test_empty_query_returns_empty_list_without_calling_service(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin, 'web')
            ->getJson('/admin/data/geocode-search')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data', []);
    }
}
