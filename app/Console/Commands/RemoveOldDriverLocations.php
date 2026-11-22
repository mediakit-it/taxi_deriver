<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DriverLocation;
use App\Models\DriverStatusPeriod;
use Carbon\Carbon;

class RemoveOldDriverLocations extends Command
{
    protected $signature = 'driver-locations:cleanup';
    protected $description = 'Удаляет записи, не обновлявшиеся более 5 минут';

    public function handle()
    {
        $threshold = Carbon::now()->subMinutes(5);

        $stale = DriverLocation::where('updated_at', '<', $threshold)->get();

        // Периоды работы (пакет №162): приложение протухшего
        // водителя замолчало, не уйдя с линии явно, - открытый
        // период «на линии» закрываем моментом последнего пинга,
        // а не моментом чистки, иначе длительность на линии
        // раздувалась бы до следующего запуска команды. Периоды
        // «не на линии» не трогаем: перерыв молчанием не кончается.
        foreach ($stale as $location) {
            DriverStatusPeriod::open()
                ->where('user_id', $location->user_id)
                ->where('status', DriverLocation::STATUS_ONLINE)
                ->update(['ended_at' => $location->updated_at]);
        }

        $count = DriverLocation::where('updated_at', '<', $threshold)->delete();

        $this->info("Удалено $count устаревших записей.");
    }
}

