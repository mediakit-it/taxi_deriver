<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;

class OrderSent implements ShouldBroadcastNow
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
    public function __construct(Order $order, ?int $userId = null, ?string $role = null)
    {
        $this->order = $order;
        $this->userId = $userId;
        $this->role = $role;
    }

    public function broadcastOn()
    {
        if ($this->userId) {
            return new Channel('user.' . $this->userId);
        }

        if ($this->role) {
            return new Channel('role.' . $this->role); // например: role.driving
        }

        // По умолчанию (если ничего не указано) можно выбросить исключение или канал general
        return new Channel('orders.general');
    }

    public function broadcastAs()
    {
        return 'sentOrder';
    }

    public function broadcastWith()
    {
        return [
            'order' => $this->order,
        ];
    }
}
