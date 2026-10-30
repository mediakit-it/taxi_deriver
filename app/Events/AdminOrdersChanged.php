<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Минимальный сигнал «заказы изменились» для страницы списка
 * заказов админки: создан заказ или у заказа сменились статус,
 * водитель или цена (ставится из OrderObserver - единой точки
 * всех изменений заказов). Канал - тот же приватный admin.alerts,
 * что слушает admin-alerts.js; payload намеренно крошечный:
 * страница по сигналу сама перечитывает свой фрагмент с текущими
 * фильтрами, актуальное состояние ей рисует сервер.
 */
class AdminOrdersChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Order $order,
    ) {}

    public function broadcastOn()
    {
        return new PrivateChannel('admin.alerts');
    }

    public function broadcastAs()
    {
        return 'orders.changed';
    }

    public function broadcastWith()
    {
        return [
            'order_id' => $this->order->id,
        ];
    }
}
