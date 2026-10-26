<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\FunctionalSettingsService;
use Illuminate\Http\JsonResponse;

class AppSettingsController extends Controller
{
    // Публичные настройки коробки для мобильного приложения:
    // брендинг (название, цвета, логотип) и контакты из раздела «Настройки».
    public function index(): JsonResponse
    {
        $settings = Setting::allCached();
        $functional = app(FunctionalSettingsService::class)->all();

        return response()->json([
            'success' => true,
            'data' => [
                'company_name' => $settings['company_name'] ?? null,
                'city' => $settings['city'] ?? null,
                'colors' => [
                    'primary' => $settings['color_primary'] ?? null,
                    'accent' => $settings['color_accent'] ?? null,
                ],
                'logo_url' => !empty($settings['logo_path'])
                    ? asset('storage/' . $settings['logo_path'])
                    : null,
                'phones' => $this->jsonList($settings['phones'] ?? null),
                'addresses' => $this->jsonList($settings['addresses'] ?? null),
                'driver_accept_seconds' => isset($settings['driver_accept_seconds']) && $settings['driver_accept_seconds'] !== ''
                    ? (int) $settings['driver_accept_seconds']
                    : null,
                // Переключатели раздела «Функционал» (только чтение):
                // режимы цены/назначения, предзаказы, источники, оценки
                'functional' => [
                    'price_mode' => $functional['price_mode'],
                    'assignment_mode' => $functional['assignment_mode'],
                    'preorders_enabled' => $functional['preorders_enabled'],
                    'order_sources' => $functional['order_sources'],
                    'ratings_enabled' => $functional['ratings_enabled'],
                ],
                'socials' => [
                    'telegram' => $settings['social_telegram'] ?? null,
                    'viber' => $settings['social_viber'] ?? null,
                    'whatsapp' => $settings['social_whatsapp'] ?? null,
                    'instagram' => $settings['social_instagram'] ?? null,
                ],
            ],
        ]);
    }

    /**
     * Настройки-массивы хранятся JSON-строками; вернуть как массив.
     */
    private function jsonList(?string $value): array
    {
        $decoded = $value ? json_decode($value, true) : null;

        return is_array($decoded) ? $decoded : [];
    }
}
