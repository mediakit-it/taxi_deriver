<?php

namespace App\Services;

use App\Models\Car;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;

// Привязка устройства водителя к автомобилю при установке приложения:
// установщик на экране входа вводит пароль привязки из настроек коробки
// и выбирает авто из списка. Привязка жёсткая: дальше устройство всегда
// работает как выбранное авто.
class CarBindingService
{
    public const STATUS_OK = 'ok';
    public const STATUS_NOT_CONFIGURED = 'not_configured';
    public const STATUS_WRONG_PASSWORD = 'wrong_password';

    /**
     * Список автомобилей для выбора при привязке - только по верному
     * паролю. Хеш пароля хранится в настройке car_bind_password и наружу
     * (в том числе в app-settings) не отдаётся.
     *
     * @return array{status: string, cars: array<int, array{id: int, label: string, plate: string}>}
     */
    public function carsForBinding(string $password): array
    {
        $hash = Setting::get('car_bind_password');

        if (!$hash) {
            return ['status' => self::STATUS_NOT_CONFIGURED, 'cars' => []];
        }

        if (!Hash::check($password, $hash)) {
            return ['status' => self::STATUS_WRONG_PASSWORD, 'cars' => []];
        }

        $cars = Car::query()
            ->orderBy('brand')
            ->orderBy('model')
            ->get()
            ->map(fn (Car $car) => [
                'id' => $car->id,
                'label' => $car->label,
                'plate' => $car->number_plate,
            ])
            ->all();

        return ['status' => self::STATUS_OK, 'cars' => $cars];
    }
}
