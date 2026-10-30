<?php

namespace App\Observers;

use App\Events\AdminAlert;
use App\Events\AdminOrdersChanged;
use App\Events\ClientOrderUpdated;
use App\Models\Order;
use App\Services\ClientNotificationService;

class OrderObserver
{
    public function created(Order $order): void
    {
        // Диспетчеры видят новый заказ в админке сразу (сигнал админки).
        event(AdminAlert::newOrder($order));

        // Страница списка заказов перечитывает свой фрагмент.
        event(new AdminOrdersChanged($order));
    }

    public function updated(Order $order): void
    {
        // Список заказов в админке живёт без перезагрузки: сигнал на
        // каждое изменение того, что список показывает.
        if ($order->wasChanged(['status', 'driver_id', 'price'])) {
            event(new AdminOrdersChanged($order));
        }

        if ($order->wasChanged('status')) {
            // Live signal for every status, so the app refreshes its
            // order list even for intermediate statuses that get no
            // notification feed entry.
            if (!empty($order->client_id)) {
                event(new ClientOrderUpdated($order));
            }

            app(ClientNotificationService::class)->notifyOrderStatusChanged($order);
        }
    }
}
