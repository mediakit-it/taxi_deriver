<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    // Заполняемые поля
    protected $fillable = [
        'client_id',
        'client_first_name',
        'client_last_name',
        'client_phone',
        'driver_id',
        'from_address',
        'from_lat',
        'from_lng',
        'to_address',
        'to_lat',
        'to_lng',
        'distance_to_client',
        'time_to_client',
        'distance',
        'duration',
        'price',
        'comment',
        'status',
        'tariff',
    ];

    // Статусы
    const STATUS_PENDING = 'pending';
    const STATUS_ASSIGNED = 'assigned';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELED = 'canceled';
    // Новые статусы RAY
    const STATUS_SEARCHING        = 'searching';
    const STATUS_SEARCHING_OTHER  = 'searching_other';
    const STATUS_ON_PLACE         = 'on_place';
    const STATUS_IN_PROGRESS      = 'in_progress';

    // Статус по умолчанию
    protected $attributes = [
        'status' => self::STATUS_PENDING,
    ];

    // Связи с другими моделями (предположим, что модель пользователя для водителей и клиентов)
    
    // Связь с клиентом (пользователь)
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    // Связь с водителем (пользователь)
    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    // Класс обслуживания заказа (тариф): orders.tariff хранит машинное
    // имя класса из cost_of_trip.name
    public function tariffClass()
    {
        return $this->belongsTo(CostOfTrip::class, 'tariff', 'name');
    }
}
