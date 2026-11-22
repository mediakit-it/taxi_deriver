<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'lat',
        'lng',
        'is_zakaz',
        'is_online',
        'order_id'
    ];

    protected $casts = [
        'is_zakaz' => 'boolean',
        'is_online' => 'boolean',
    ];

    // Статусы работы водителя: явный флаг ухода с линии (is_online),
    // дальше - свежесть последней геопозиции
    const STATUS_ONLINE = 'online';
    const STATUS_OFFLINE = 'offline';
    const STATUS_NOT_WORKING = 'not_working';

    const STATUS_LABELS = [
        self::STATUS_ONLINE => 'На линии',
        self::STATUS_OFFLINE => 'Не на линии',
        self::STATUS_NOT_WORKING => 'Не работает',
    ];

    // Пинг свежее 5 минут - на линии; свежее суток - не на линии;
    // старше суток или без времени - не работает
    public function getWorkStatusAttribute(): string
    {
        // Явный уход с линии важнее свежести: водитель на перерыве
        // может не слать пинги, и статус не должен «протухать»
        // в «не работает» - он не на линии, пока сам не вернётся.
        if ($this->is_online === false) {
            return self::STATUS_OFFLINE;
        }

        if ($this->updated_at === null) {
            return self::STATUS_NOT_WORKING;
        }

        if ($this->updated_at->gte(now()->subMinutes(5))) {
            return self::STATUS_ONLINE;
        }

        if ($this->updated_at->gte(now()->subDay())) {
            return self::STATUS_OFFLINE;
        }

        return self::STATUS_NOT_WORKING;
    }

    // Подпись статуса работы
    public function getWorkStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->work_status] ?? $this->work_status;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
