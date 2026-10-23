<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Lightweight live signal for the client app: an order changed status.
 * Sent for EVERY status (unlike ClientNotification, which is only
 * recorded for the statuses the client gets a feed entry for), so the
 * app can refresh its order list and status card in real time.
 */
class ClientOrderUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Order $order
    ) {}

    public function broadcastOn()
    {
        return new Channel('user.'.$this->order->client_id);
    }

    public function broadcastAs()
    {
        return 'orderUpdated';
    }

    public function broadcastWith()
    {
        return [
            'order_id' => $this->order->id,
            'status' => $this->order->status,
        ];
    }
}
