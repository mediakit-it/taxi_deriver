<?php

namespace App\Events;

use App\Models\Message;
use App\Models\Order;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

/**
 * Сигнал для админки (диспетчеров) в реальном времени: водитель не
 * ответил на назначенный заказ, пришёл новый заказ или клиент написал
 * в поддержку. Транслируется в приватный канал admin.alerts; фронтенд
 * админки строит по нему тосты и панель «Требуют внимания».
 * Контракт payload - broadcastWith() ниже.
 */
class AdminAlert implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public const TYPE_DRIVER_NO_RESPONSE = 'driver_no_response';
    public const TYPE_NEW_ORDER = 'new_order';
    public const TYPE_NEW_MESSAGE = 'new_message';
    public const TYPE_DRIVER_DECLINED = 'driver_declined';

    public function __construct(
        public string $type,
        public string $title,
        public string $text,
        public ?int $orderId = null,
        public ?int $messageId = null,
        public ?string $driverName = null,
        public ?string $clientName = null,
        public ?string $fromAddress = null,
        public ?string $toAddress = null,
        public ?int $waitingSeconds = null,
        public ?string $url = null,
        public ?int $userId = null,
    ) {}

    /**
     * Водитель не ответил на назначенный заказ за driver_accept_seconds.
     */
    public static function driverNoResponse(Order $order): self
    {
        $driverName = self::userName($order->driver);

        return new self(
            type: self::TYPE_DRIVER_NO_RESPONSE,
            title: 'Водитель не ответил',
            text: "Водитель {$driverName} не ответил на заказ #{$order->id} - нужен новый водитель.",
            orderId: $order->id,
            driverName: $driverName,
            clientName: self::fullName($order->client_first_name, $order->client_last_name),
            fromAddress: $order->from_address,
            toAddress: $order->to_address,
            waitingSeconds: $order->driver_waiting_seconds,
            url: route('order.edit', $order->id),
        );
    }

    /**
     * Создан новый заказ (из приложения, диспетчером в админке или
     * водителем «от борта»). Заказ от борта - не «новый заказ» для
     * диспетчера, а уже взятая поездка: заголовок и текст говорят,
     * какой водитель его взял (замечание Кати 22.11.2026).
     */
    public static function newOrder(Order $order): self
    {
        if ($order->source === Order::SOURCE_CURB) {
            $driverName = self::userName($order->driver);

            return new self(
                type: self::TYPE_NEW_ORDER,
                title: 'Заказ от борта',
                text: "Водитель {$driverName} взял заказ от борта #{$order->id}: {$order->from_address} → {$order->to_address}",
                orderId: $order->id,
                driverName: $driverName,
                clientName: self::fullName($order->client_first_name, $order->client_last_name),
                fromAddress: $order->from_address,
                toAddress: $order->to_address,
                url: route('order.edit', $order->id),
            );
        }

        return new self(
            type: self::TYPE_NEW_ORDER,
            title: 'Новый заказ',
            text: "Заказ #{$order->id}: {$order->from_address} → {$order->to_address}",
            orderId: $order->id,
            driverName: self::userName($order->driver),
            clientName: self::fullName($order->client_first_name, $order->client_last_name),
            fromAddress: $order->from_address,
            toAddress: $order->to_address,
            url: route('order.edit', $order->id),
        );
    }

    /**
     * Клиент написал сообщение в поддержку.
     */
    public static function newMessage(Message $message): self
    {
        return new self(
            type: self::TYPE_NEW_MESSAGE,
            title: 'Новое сообщение',
            text: Str::limit((string) $message->message, 140),
            messageId: $message->id,
            clientName: self::userName($message->user),
            url: route('messenger'),
            userId: $message->user_id,
        );
    }

    /**
     * Водитель отказался от заказа, назначенного ему диспетчером лично:
     * диспетчеру нужен новый водитель (сигнал аналогичен driver_no_response,
     * но мгновенный - по факту отказа, а не по таймауту).
     */
    public static function driverDeclined(Order $order): self
    {
        $driverName = self::userName($order->driver);

        return new self(
            type: self::TYPE_DRIVER_DECLINED,
            title: 'Водитель отказался от заказа',
            text: "Водитель {$driverName} отказался от заказа #{$order->id} - нужен новый водитель.",
            orderId: $order->id,
            driverName: $driverName,
            clientName: self::fullName($order->client_first_name, $order->client_last_name),
            fromAddress: $order->from_address,
            toAddress: $order->to_address,
            url: route('order.edit', $order->id),
        );
    }

    public function broadcastOn()
    {
        return new PrivateChannel('admin.alerts');
    }

    public function broadcastAs()
    {
        return 'admin.alert';
    }

    public function broadcastWith()
    {
        return [
            'type' => $this->type,
            'order_id' => $this->orderId,
            'message_id' => $this->messageId,
            'title' => $this->title,
            'text' => $this->text,
            'driver_name' => $this->driverName,
            'client_name' => $this->clientName,
            'from_address' => $this->fromAddress,
            'to_address' => $this->toAddress,
            'waiting_seconds' => $this->waitingSeconds,
            'url' => $this->url,
            'user_id' => $this->userId,
        ];
    }

    private static function userName(?User $user): ?string
    {
        return $user === null
            ? null
            : self::fullName($user->first_name, $user->last_name);
    }

    private static function fullName(?string $firstName, ?string $lastName): ?string
    {
        $name = trim(($firstName ?? '').' '.($lastName ?? ''));

        return $name === '' ? null : $name;
    }
}
