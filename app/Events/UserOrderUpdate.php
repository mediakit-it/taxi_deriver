<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserOrderUpdate implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $message;

    /**
     * @param Order $order
     * @param int|null $userId - ID конкретного пользователя (если нужен)
     * @param string|null $role - роль, если отправлять по роли
     */
    public function __construct(int $userId, String $message)
    {
        $this->userId = $userId;
        $this->message = $message;
    }

    public function broadcastOn()
    {
            return new Channel('user.'.$this->userId); 
    }

    public function broadcastAs()
    {
        return 'sentOrderUpdateUser';
    }

    public function broadcastWith()
    {
        return [
            'message' => $this->message
        ];
    }
}
