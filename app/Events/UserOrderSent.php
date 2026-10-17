<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserOrderSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;

    /**
     * @param Order $order
     * @param int|null $userId - ID конкретного пользователя (если нужен)
     * @param string|null $role - роль, если отправлять по роли
     */
    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    public function broadcastOn()
    {
            return new Channel('user.'.$this->userId); 
    }

    public function broadcastAs()
    {
        return 'sentOrderAssigned';
    }
}
