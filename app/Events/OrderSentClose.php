<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;

class OrderSentClose implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;
    public $userId;
    public $role;

    /**
     * @param Order $order
     * @param int|null $userId - ID конкретного пользователя (если нужен)
     * @param string|null $role - роль, если отправлять по роли
     */
    public function __construct(Order $order, int $userId)
    {
        $this->order = $order;
        $this->userId = $userId;
    }

    public function broadcastOn()
    {
            return new Channel('role.driving'); // например: role.driving
    }

    public function broadcastAs()
    {
        return 'sentOrderClose';
    }

    public function broadcastWith()
    {
        return [
            'order' => $this->order,
            'userId' => $this->userId,
        ];
    }
}
