<?php

namespace Tests\Feature\Api;

use App\Models\CostOfTrip;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CostOfTripTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Значения прода: City - '8', Outside_the_City - '1,70'. После перевода
     * цены в decimal мобильное приложение должно получать те же строки
     * и тот же набор ключей - это контракт продакшн-приложения.
     */
    private function seedProdTariffs(): void
    {
        CostOfTrip::create(['name' => 'City', 'price' => 8, 'display_name' => 'По городу']);
        CostOfTrip::create(['name' => 'Outside_the_City', 'price' => 1.70, 'display_name' => 'За городом']);
    }

    public function test_index_keeps_legacy_keys_and_string_prices(): void
    {
        $this->seedProdTariffs();

        $response = $this->getJson('/api/v1/cost-of-trip');

        $response->assertOk();
        $response->assertJsonPath('success', true);

        $data = $response->json('data');
        $this->assertCount(2, $data);

        foreach ($data as $row) {
            $keys = array_keys($row);
            sort($keys);
            // display_name в API не отдаём - ключи ровно как до конвертации.
            $this->assertSame(['created_at', 'id', 'name', 'price', 'updated_at'], $keys);
            $this->assertIsString($row['price']);
        }

        $prices = collect($data)->pluck('price', 'name')->all();
        $this->assertSame('8', $prices['City']);
        $this->assertSame('1,70', $prices['Outside_the_City']);
    }

    public function test_get_by_name_returns_legacy_string_price(): void
    {
        $this->seedProdTariffs();

        $response = $this->getJson('/api/v1/cost-of-trip/Outside_the_City');

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.name', 'Outside_the_City');
        $response->assertJsonPath('data.price', '1,70');
    }

    public function test_price_is_stored_as_decimal(): void
    {
        $tariff = CostOfTrip::create(['name' => 'City', 'price' => '1.7']);

        $this->assertSame('1.70', $tariff->fresh()->price);
    }
}
