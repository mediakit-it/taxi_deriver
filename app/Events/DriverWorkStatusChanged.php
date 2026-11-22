<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Живой сигнал «водитель сменил статус работы» для админки
 * (пакет №162): переход «на линии / не на линии» виден без
 * перезагрузки - список водителей обновляет бейдж, список
 * заказов перечитывает фрагмент с селектами назначения, карта
 * перекрашивает метку. Канал - тот же приватный admin.alerts,
 * что слушает admin-alerts.js; payload - сам статус, его
 * подпись, начало текущего периода и флаг занятости заказом:
 * страницам этого достаточно, сервер перечитывать не надо.
 */
class DriverWorkStatusChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $userId,
        public string $workStatus,
        public string $workStatusLabel,
        public ?string $statusSince,
        public bool $isZakaz,
    ) {}

    public function broadcastOn()
    {
        return new PrivateChannel('admin.alerts');
    }

    public function broadcastAs()
    {
        return 'driver.status';
    }

    public function broadcastWith()
    {
        return [
            'user_id' => $this->userId,
            'work_status' => $this->workStatus,
            'work_status_label' => $this->workStatusLabel,
            'status_since' => $this->statusSince,
            'is_zakaz' => $this->isZakaz,
        ];
    }
}
