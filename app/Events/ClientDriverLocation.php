<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Live driver position for the client app: the client watches
 * the car approach on the map during an active trip. Fired on
 * every location ping of a driver who has an active order, to
 * that order's client channel.
 */
class ClientDriverLocation implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $clientId,
        public int $orderId,
        public float $lat,
        public float $lng
    ) {}

    public function broadcastOn()
    {
        return new Channel('user.'.$this->clientId);
    }

    public function broadcastAs()
    {
        return 'driverLocation';
    }

    public function broadcastWith()
    {
        return [
            'order_id' => $this->orderId,
            'lat' => $this->lat,
            'lng' => $this->lng,
        ];
    }
}
