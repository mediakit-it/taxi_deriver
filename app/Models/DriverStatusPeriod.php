<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Период непрерывной работы водителя в одном статусе («на линии»
// или «не на линии»): открывается переходом статуса в пинге
// геопозиции (SyncWorkStatusPeriod), закрывается следующим
// переходом; открытый период «на линии» может закрыть и чистка
// протухших локаций - моментом последнего пинга водителя.
class DriverStatusPeriod extends Model
{
    // Значения статусов периодов - те же, что у DriverLocation
    const STATUS_ONLINE = DriverLocation::STATUS_ONLINE;
    const STATUS_OFFLINE = DriverLocation::STATUS_OFFLINE;

    protected $fillable = [
        'user_id',
        'status',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    // Открытый период - статус, в котором водитель находится сейчас
    public function scopeOpen($query)
    {
        return $query->whereNull('ended_at');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Длительность от начала периода до сейчас строкой:
    // «N мин» до часа, дальше «H ч MM мин»
    public function durationLabel(): string
    {
        $minutes = (int) $this->started_at->diffInMinutes(now());

        if ($minutes < 60) {
            return $minutes . ' мин';
        }

        return intdiv($minutes, 60) . ' ч '
            . str_pad((string) ($minutes % 60), 2, '0', STR_PAD_LEFT) . ' мин';
    }
}
