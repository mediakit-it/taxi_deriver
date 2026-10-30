<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageAdminSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function broadcastOn()
    {
        return new Channel('admin'); // Название канала должно совпадать
    }

    public function broadcastAs()
    {
        return 'sendMailAdmin'; // Имя события
    }

    public function broadcastWith()
    {
        $user = $this->message->user;

        return [
            'message' => $this->message,
            // Отправитель для тоста в админке: имя + превью текста
            // позволяют диспетчеру понять, кто написал, без открытия
            // страницы сообщений.
            'user' => [
                'id' => (int) $this->message->user_id,
                'name' => $user
                    ? trim($user->first_name.' '.$user->last_name)
                    : '',
            ],
        ];
    }
}
