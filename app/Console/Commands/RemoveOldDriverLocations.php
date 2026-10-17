<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DriverLocation;
use Carbon\Carbon;

class RemoveOldDriverLocations extends Command
{
    protected $signature = 'driver-locations:cleanup';
    protected $description = 'Удаляет записи, не обновлявшиеся более 5 минут';

    public function handle()
    {
        $threshold = Carbon::now()->subMinutes(5);

        $count = DriverLocation::where('updated_at', '<', $threshold)->delete();

        $this->info("Удалено $count устаревших записей.");
    }
}

