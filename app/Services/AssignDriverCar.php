<?php

namespace App\Services;

use App\Models\Car;
use App\Models\User;
use Illuminate\Support\Facades\DB;

// Назначение автомобиля водителю из админки. Это та же привязка
// устройства (users.current_car_id), которую водительское
// приложение докладывает само после входа (CarBindingService):
// диспетчер может поправить её вручную со стороны водителя или
// со стороны автомобиля. Привязка одна на автомобиль: если авто
// было закреплено за другим водителем, у него она снимается в
// той же транзакции.
class AssignDriverCar
{
    // Закрепить авто за водителем; null - снять привязку водителя
    public function execute(User $driver, ?int $carId): void
    {
        DB::transaction(function () use ($driver, $carId) {
            if ($carId !== null) {
                User::where('current_car_id', $carId)
                    ->where('id', '!=', $driver->id)
                    ->update(['current_car_id' => null]);
            }

            $driver->current_car_id = $carId;
            $driver->save();
        });
    }

    // Снять привязку автомобиля (со стороны карточки авто)
    public function releaseCar(Car $car): void
    {
        User::where('current_car_id', $car->id)
            ->update(['current_car_id' => null]);
    }
}
