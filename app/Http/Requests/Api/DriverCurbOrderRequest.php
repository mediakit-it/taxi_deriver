<?php

namespace App\Http\Requests\Api;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

// Создание заказа «от борта» водительским приложением
// (POST /api/v1/driver/orders): пассажир сел с улицы, водитель сам
// заводит заказ. Создавать может только водитель - остальные роли
// получают 403 на уровне authorize(), как в CarBindingReportRequest.
// Деловые условия (привязанное авто, точка подачи) проверяет
// контроллер: ответам нужны свои тексты ошибок.
class DriverCurbOrderRequest extends FormRequest
{

    /** Назначение ближе этого радиуса к точке подачи - не поездка. */
    private const PICKUP_MATCH_RADIUS_M = 30;

    public function authorize(): bool
    {
        return $this->user()?->role === User::ROLE_DRIVER;
    }

    public function rules(): array
    {
        return [
            'to_lat' => [
                'required',
                'numeric',
                'between:-90,90',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (! is_numeric($value) || ! is_numeric($this->input('to_lng'))) {
                        return;
                    }

                    $toLat = (float) $value;
                    $toLng = (float) $this->input('to_lng');

                    // (0, 0) - точка в океане, а не адрес: приложение
                    // присылает её, когда точку назначения так и не
                    // выбрали на карте. Такой заказ диспетчеру не нужен.
                    if ($toLat === 0.0 && $toLng === 0.0) {
                        $fail('Укажите точку назначения: координаты (0, 0) не являются адресом.');

                        return;
                    }

                    // Назначение, совпадающее с точкой подачи, - не
                    // поездка: пассажир уже на месте, ехать некуда.
                    $pickup = $this->pickupPoint();

                    if ($pickup !== null
                        && $this->distanceMeters($pickup[0], $pickup[1], $toLat, $toLng) < self::PICKUP_MATCH_RADIUS_M) {
                        $fail('Точка назначения совпадает с точкой подачи. Укажите, куда нужно ехать.');
                    }
                },
            ],
            'to_lng' => ['required', 'numeric', 'between:-180,180'],
            'to_address' => ['required', 'string', 'max:255'],
            'from_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'from_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'from_address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'comment' => ['nullable', 'string'],
            'distance' => ['nullable', 'integer', 'min:0'],
            'duration' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * Точка подачи для сравнения с назначением: координаты из
     * запроса, а если приложение их не прислало - последняя
     * известная геопозиция водителя (тот же fallback, что и в
     * DriverOrderController).
     */
    private function pickupPoint(): ?array
    {
        $fromLat = $this->input('from_lat');
        $fromLng = $this->input('from_lng');

        if (is_numeric($fromLat) && is_numeric($fromLng)) {
            return [(float) $fromLat, (float) $fromLng];
        }

        $location = $this->user()?->location;

        if ($location && is_numeric($location->lat) && is_numeric($location->lng)) {
            return [(float) $location->lat, (float) $location->lng];
        }

        return null;
    }

    private function distanceMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return 2 * 6371000.0 * asin(min(1.0, sqrt($a)));
    }
}
