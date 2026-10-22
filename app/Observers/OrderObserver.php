<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\ClientNotificationService;

class OrderObserver
{
    public function updated(Order $order): void
    {
        if ($order->wasChanged('status')) {
            app(ClientNotificationService::class)->notifyOrderStatusChanged($order);
        }
    }
}
