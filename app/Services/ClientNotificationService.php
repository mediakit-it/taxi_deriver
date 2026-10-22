<?php

namespace App\Services;

use App\Events\ClientNotificationCreated;
use App\Models\ClientNotification;
use App\Models\Message;
use App\Models\Order;
use Illuminate\Support\Str;

class ClientNotificationService
{
    /**
     * Order statuses the client should hear about,
     * with their notification titles.
     */
    private const ORDER_STATUS_TITLES = [
        Order::STATUS_ASSIGNED => 'Водитель назначен',
        Order::STATUS_ON_PLACE => 'Водитель на месте',
        Order::STATUS_IN_PROGRESS => 'Поездка началась',
        Order::STATUS_COMPLETED => 'Поездка завершена',
        Order::STATUS_CANCELED => 'Заказ отменён',
    ];

    public function notifyOrderStatusChanged(Order $order): ?ClientNotification
    {
        $title = self::ORDER_STATUS_TITLES[$order->status] ?? null;

        if ($title === null || empty($order->client_id)) {
            return null;
        }

        return $this->create(
            userId: $order->client_id,
            type: 'order_status',
            title: $title,
            body: "Заказ #{$order->id}: {$order->from_address} → {$order->to_address}",
            orderId: $order->id,
        );
    }

    public function notifySupportReply(Message $message): ClientNotification
    {
        return $this->create(
            userId: $message->user_id,
            type: 'support',
            title: 'Ответ поддержки',
            body: Str::limit((string) $message->message, 140),
            orderId: null,
        );
    }

    private function create(int $userId, string $type, string $title, ?string $body, ?int $orderId): ClientNotification
    {
        $notification = ClientNotification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'order_id' => $orderId,
        ]);

        event(new ClientNotificationCreated($notification));

        return $notification;
    }
}
