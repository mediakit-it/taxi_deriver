<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Services\TelegramService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class SendTelegramOrderNotification implements ShouldQueue
{
    protected $telegram;

    public function __construct(TelegramService $telegram)
    {
        $this->telegram = $telegram;
    }

    public function handle(OrderCreated $event)
    {
        try {
            // Получаем последний созданный заказ
            $order = \App\Models\Order::latest()->first();
            
            if (!$order) {
                return;
            }

            // ✅ ПРОВЕРКА 1: Статус должен быть 'pending' (новый заказ)
            if ($order->status !== \App\Models\Order::STATUS_PENDING) {
                return; // Если статус не 'pending' - не отправляем
            }

            // ✅ ПРОВЕРКА 2: Заказ должен быть создан не более 10 секунд назад
            if ($order->created_at->diffInSeconds(now()) > 10) {
                return; // Если заказ старый - не отправляем
            }

            // ✅ ПРОВЕРКА 3: Нет назначенного водителя
            if ($order->driver_id !== null) {
                return; // Если водитель уже назначен - не отправляем
            }

            // Отправляем сообщение
            $message = "🚕 <b>НОВЫЙ ЗАКАЗ!</b>\n\n";
            $message .= "👤 Клиент: {$order->client_first_name} {$order->client_last_name}\n";
            $message .= "📱 Телефон: {$order->client_phone}\n";
            $message .= "📍 Откуда: {$order->from_address}\n";
            $message .= "🏁 Куда: {$order->to_address}\n";
            $message .= "🆔 ID заказа: #{$order->id}\n";
            $message .= "📅 Дата: " . now()->format('d.m.Y H:i');

            $this->telegram->sendMessage($message);
            
        } catch (\Exception $e) {
            // Логируем ошибку, но не даем упасть созданию заказа
            Log::error('Ошибка в Telegram уведомлении: ' . $e->getMessage());
        }
    }
}