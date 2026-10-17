<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Order $order,
        public string $reason = 'status_changed'
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('admin.orders');
    }

    public function broadcastAs(): string
    {
        return 'OrderStatusChanged';
    }

    public function broadcastWith(): array
    {
        return [
            'order_id'  => $this->order->id,
            'status'    => $this->order->status,
            'driver_id' => $this->order->driver_id,
            'reason'    => $this->reason,
        ];
    }
}