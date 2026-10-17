<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;

class OrderSentNewDriving implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    /**
     * @param Order $order
     * @param int|null $userId - ID конкретного пользователя (если нужен)
     * @param string|null $role - роль, если отправлять по роли
     */
    public function __construct( String $message)
    {
        $this->message = $message;
    }

    public function broadcastOn()
    {
            return new Channel('role.driving'); // например: role.driving
    }

    public function broadcastAs()
    {
        return 'OrderSentNewDriving';
    }

    public function broadcastWith()
    {
        return [
            'message' => $this->message
        ];
    }
}
