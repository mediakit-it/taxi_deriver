<?php

namespace App\Services;

use App\Models\Car;
use App\Models\Setting;
use App\Models\User;
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
    public const STATUS_CAR_NOT_FOUND = 'car_not_found';

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
    /**
     * Доклад авторизованного водителя о привязанном к его устройству
     * авто: сервер фиксирует users.current_car_id, чтобы диспетчер
     * видел в админке, на какой машине водитель сейчас работает.
     * Существование авто проверяется здесь, а не правилом exists -
     * ответу об ошибке нужен свой текст («Автомобиль не найден»).
     *
     * @return array{status: string, car: array{car_id: int, label: string, plate: string}|null}
     */
    public function reportCurrentCar(User $user, int $carId): array
    {
        $car = Car::find($carId);

        if (!$car) {
            return ['status' => self::STATUS_CAR_NOT_FOUND, 'car' => null];
        }

        $user->current_car_id = $car->id;
        $user->save();

        return ['status' => self::STATUS_OK, 'car' => [
            'car_id' => $car->id,
            'label' => $car->label,
            'plate' => $car->number_plate,
        ]];
    }
}
