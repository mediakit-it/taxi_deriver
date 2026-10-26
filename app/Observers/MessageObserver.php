<?php

namespace App\Observers;

use App\Events\AdminAlert;
use App\Models\Message;
use App\Services\ClientNotificationService;

class MessageObserver
{
    public function created(Message $message): void
    {
        // Only support replies (type "admin") notify the client;
        // the client's own messages need no notification.
        if ($message->type === 'admin') {
            app(ClientNotificationService::class)->notifySupportReply($message);
        }

        // Сообщения клиентов диспетчеры видят в админке сразу.
        if ($message->type === 'user') {
            event(AdminAlert::newMessage($message));
        }
    }
}
