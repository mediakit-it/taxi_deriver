<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Services\YandexGeocodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

// Подсказки адресов для форм панели (создание заказа и т.п.):
// тот же бэкенд-геокодер, что и у мобильного API
// (GET /api/v1/geocode), но под сессионной авторизацией админки -
// API-роут закрыт JWT и из браузера панели недоступен. Ответ
// повторяет форму API-списка: {success: true, data: [точки]}.
class GeocodeController extends Controller
{
    public function search(Request $request, YandexGeocodeService $geocoder): JsonResponse
    {
        $address = trim((string) $request->query('address', ''));

        if ($address === '') {
            return response()->json(['success' => true, 'data' => []]);
        }

        if (! $geocoder->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'Geocoder is not configured',
            ], 503);
        }

        $limit = (int) $request->query('limit', YandexGeocodeService::MAX_RESULTS);

        return response()->json([
            'success' => true,
            'data' => $geocoder->geocodeMany($address, $limit),
        ]);
    }
}
