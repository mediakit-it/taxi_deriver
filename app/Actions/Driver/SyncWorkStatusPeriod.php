<?php

namespace App\Actions\Driver;

use App\Models\DriverLocation;
use App\Models\DriverStatusPeriod;
use App\Models\User;

/**
 * Синхронизация периода работы водителя с его фактическим
 * статусом «на линии / не на линии» (пакет №162) - единственная
 * точка этой операции: её зовёт пинг геопозиции при переходе
 * флага is_online. Открытый период с тем же статусом не трогаем:
 * пинги идут каждые 10 секунд, а период - один на весь
 * непрерывный отрезок статуса. При смене статуса открытый
 * период закрывается моментом перехода и открывается новый.
 */
class SyncWorkStatusPeriod
{
    public function sync(User $driver, bool $isOnline): DriverStatusPeriod
    {
        $target = $isOnline ? DriverLocation::STATUS_ONLINE : DriverLocation::STATUS_OFFLINE;

        $open = DriverStatusPeriod::open()
            ->where('user_id', $driver->id)
            ->orderByDesc('started_at')
            ->first();

        if ($open && $open->status === $target) {
            return $open;
        }

        $now = now();

        if ($open) {
            // Закрываем все открытые периоды водителя, а не только
            // последний: дублей открытых периодов не должно быть
            // в принципе, но если данные разошлись, переход
            // статуса - правильное место их подчистить.
            DriverStatusPeriod::open()
                ->where('user_id', $driver->id)
                ->update(['ended_at' => $now]);
        }

        return DriverStatusPeriod::create([
            'user_id' => $driver->id,
            'status' => $target,
            'started_at' => $now,
        ]);
    }
}
