<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YandexController extends Controller
{
    public function suggest(Request $request)
    {
        // Логируем входящий запрос
        Log::channel('yandex')->info('YANDEX_SUGGEST_REQUEST', [
            'text' => $request->input('text'),
            'timestamp' => now()->toDateTimeString(),
            'ip' => $request->ip()
        ]);

        try {
            $apiKey = config('services.yandex.suggest_key');
            
            // Проверяем наличие API ключа
            if (empty($apiKey)) {
                Log::channel('yandex')->error('YANDEX_API_KEY_MISSING', [
                    'message' => 'API ключ не найден в конфигурации (services.yandex.suggest_key)'
                ]);
                return response()->json(['error' => 'API ключ не настроен'], 503);
            }

            $text = $request->input('text');
            
            if (empty($text)) {
                Log::channel('yandex')->warning('YANDEX_SUGGEST_EMPTY_TEXT', [
                    'message' => 'Текст для поиска не передан'
                ]);
                return response()->json(['error' => 'Текст для поиска не указан'], 400);
            }

            // Логируем запрос к Яндекс
            Log::channel('yandex')->info('YANDEX_API_REQUEST', [
                'url' => 'https://suggest-maps.yandex.ru/v1/suggest',
                'params' => [
                    'apikey' => substr($apiKey, 0, 10) . '...', // скрываем ключ
                    'text' => $text,
                    'types' => 'geo',
                    'lang' => 'ru_RU',
                    'results' => 5
                ]
            ]);

            $response = Http::timeout(10)->get('https://suggest-maps.yandex.ru/v1/suggest', [
                'apikey' => $apiKey,
                'types' => 'geo',
                'text' => $text,
                'lang' => 'ru_RU',
                'results' => 5,
                'origin' => 'jsapi2Geocoder',
                'print_address' => 1,
                'bbox' => '-180,-90,180,90',
            ]);

            // Логируем ответ от Яндекса
            Log::channel('yandex')->info('YANDEX_API_RESPONSE', [
                'status' => $response->status(),
                'success' => $response->successful(),
                'text' => $text,
                'response_size' => strlen($response->body())
            ]);

            if (!$response->successful()) {
                Log::channel('yandex')->error('YANDEX_API_ERROR', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'text' => $text
                ]);
                
                return response()->json([
                    'error' => 'Ошибка при запросе к Яндекс API',
                    'status' => $response->status()
                ], $response->status());
            }

            // Проверяем структуру ответа
            $jsonResponse = $response->json();
            
            Log::channel('yandex')->info('YANDEX_SUGGEST_SUCCESS', [
                'text' => $text,
                'results_count' => isset($jsonResponse['results']) ? count($jsonResponse['results']) : 0,
                'has_results' => isset($jsonResponse['results']) && count($jsonResponse['results']) > 0
            ]);

            return response($response->body(), $response->status())
                ->header('Content-Type', 'application/json');

        } catch (\Exception $e) {
            Log::channel('yandex')->error('YANDEX_SUGGEST_EXCEPTION', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Внутренняя ошибка сервера',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
