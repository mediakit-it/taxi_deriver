<?php

namespace App\Events;

use App\Models\ClientNotification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClientNotificationCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ClientNotification $notification
    ) {}

    public function broadcastOn()
    {
        return new Channel('user.'.$this->notification->user_id);
    }

    public function broadcastAs()
    {
        return 'notificationCreated';
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->notification->id,
            'type' => $this->notification->type,
            'title' => $this->notification->title,
            'body' => $this->notification->body,
            'order_id' => $this->notification->order_id,
            'created_at' => $this->notification->created_at?->toIso8601String(),
        ];
    }
}
