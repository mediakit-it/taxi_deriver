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
        'driver_offered_at',
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

    protected $casts = [
        'driver_offered_at' => 'datetime',
    ];

    // Подписи статусов для админки (те же, что в списках заказов)
    const STATUS_LABELS = [
        self::STATUS_PENDING => 'Ожидание',
        self::STATUS_SEARCHING => 'Ищем водителя',
        self::STATUS_SEARCHING_OTHER => 'Ищем другого водителя',
        self::STATUS_ASSIGNED => 'Водитель в пути',
        self::STATUS_ON_PLACE => 'Водитель на месте',
        self::STATUS_IN_PROGRESS => 'В дороге',
        self::STATUS_COMPLETED => 'Завершено',
        self::STATUS_CANCELED => 'Отменено',
    ];

    /**
     * Карта статус => подпись для всех статусов.
     *
     * @return array<string, string>
     */
    public static function statusLabels(): array
    {
        return self::STATUS_LABELS;
    }

    // Человекочитаемая подпись текущего статуса
    public function getStatusLabelAttribute(): ?string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

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

    // Дистанция поездки в километрах (в базе хранится в метрах)
    public function getDistanceKmAttribute(): ?float
    {
        return $this->distance === null ? null : round($this->distance / 1000, 2);
    }

    // Длительность поездки в целых минутах (в базе хранится в секундах)
    public function getDurationMinAttribute(): ?int
    {
        return $this->duration === null ? null : (int) round($this->duration / 60);
    }

    // Дистанция до клиента в километрах (в базе хранится в метрах)
    public function getDistanceToClientKmAttribute(): ?float
    {
        return $this->distance_to_client === null ? null : round($this->distance_to_client / 1000, 2);
    }

    // Время до клиента в целых минутах (в базе хранится в секундах)
    public function getTimeToClientMinAttribute(): ?int
    {
        return $this->time_to_client === null ? null : (int) round($this->time_to_client / 60);
    }

    // Класс обслуживания заказа (тариф): orders.tariff хранит машинное
    // имя класса из cost_of_trip.name
    public function tariffClass()
    {
        return $this->belongsTo(CostOfTrip::class, 'tariff', 'name');
    }

    /**
     * Заказы, ожидающие ответа назначенного водителя дольше таймаута:
     * заказ предложил диспетчер конкретному водителю, а тот не
     * отреагировал (реакция = заказ ушёл из статуса searching).
     */
    public function scopeAwaitingDriverResponse($query, int $timeoutSeconds)
    {
        return $query->where('status', self::STATUS_SEARCHING)
            ->whereNotNull('driver_id')
            ->whereNotNull('driver_offered_at')
            ->where('driver_offered_at', '<=', now()->subSeconds($timeoutSeconds));
    }

    /**
     * Таймаут ответа водителя из настроек (driver_accept_seconds).
     */
    public static function driverAcceptTimeout(): int
    {
        $timeout = (int) Setting::get('driver_accept_seconds', 60);

        return $timeout > 0 ? $timeout : 60;
    }

    // Сколько секунд заказ уже ждёт ответа назначенного водителя
    public function getDriverWaitingSecondsAttribute(): int
    {
        return $this->driver_offered_at
            ? (int) $this->driver_offered_at->diffInSeconds(now(), true)
            : 0;
    }
}
