<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('driver-locations:cleanup')->everyMinute();
   
        // РАСКОММЕНТИТЬ КАК ТОЛЬКО ОБНОВЯТ ПРИЛОЖЕНИЕ! RAY
     // $schedule->call(function () {
    //     $orders = \App\Models\Order::where('status', \App\Models\Order::STATUS_SEARCHING)
    //         ->where('updated_at', '<=', now()->subMinute())
    //         ->get();

    //     foreach ($orders as $order) {
    //         $order->update([
    //             'driver_id' => null,
    //             'status'    => \App\Models\Order::STATUS_SEARCHING_OTHER,
    //         ]);
    //         event(new \App\Events\OrderStatusChanged($order, 'driver_timeout'));
    //     }
    // })->everyMinute();
    
        }



    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        

        require base_path('routes/console.php');
    }
}
