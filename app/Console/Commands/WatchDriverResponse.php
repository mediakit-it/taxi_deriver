<?php

namespace App\Console\Commands;

use App\Events\AdminAlert;
use App\Models\Order;
use Illuminate\Console\Command;

/**
 * Сторож ответов водителей: находит заказы, где назначенный диспетчером
 * водитель не отреагировал за driver_accept_seconds из настроек, и
 * шлёт диспетчерам сигнал в админку. Сами заказы НЕ меняет (одобренный
 * дизайн: только сигнал, без авто-снятия водителя). Запускается из
 * планировщика каждую минуту; сигнал повторяется, пока заказ висит
 * без ответа, - панель в админке строится по /admin/data/overdue-orders.
 */
class WatchDriverResponse extends Command
{
    protected $signature = 'orders:watch-driver-response';

    protected $description = 'Сигналит диспетчерам о водителях, не ответивших на назначенный заказ вовремя';

    public function handle(): int
    {
        $orders = Order::awaitingDriverResponse(Order::driverAcceptTimeout())
            ->with('driver')
            ->get();

        foreach ($orders as $order) {
            event(AdminAlert::driverNoResponse($order));
        }

        $this->info("Заказов без ответа водителя: {$orders->count()}");

        return self::SUCCESS;
    }
}
