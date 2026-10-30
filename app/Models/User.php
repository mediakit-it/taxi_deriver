<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    // Все роли системы (users.role - строка)
    public const ROLE_SUPERADMIN = 'superadmin';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_MANAGER = 'manager';
    public const ROLE_DISPATCHER = 'dispatcher';
    public const ROLE_DRIVER = 'driving';
    public const ROLE_CLIENT = 'user';

    // Подписи ролей для админки
    public const ROLE_LABELS = [
        self::ROLE_SUPERADMIN => 'Суперадмин',
        self::ROLE_ADMIN => 'Администратор',
        self::ROLE_MANAGER => 'Менеджер',
        self::ROLE_DISPATCHER => 'Диспетчер',
        self::ROLE_DRIVER => 'Водитель',
        self::ROLE_CLIENT => 'Клиент',
    ];

    // Роли, которым открыт вход в веб-панель (/authorization)
    public const PANEL_ROLES = [
        self::ROLE_SUPERADMIN,
        self::ROLE_ADMIN,
        self::ROLE_MANAGER,
        self::ROLE_DISPATCHER,
    ];

    // Роли, которые можно назначить через админку. superadmin -
    // только представитель нашей компании: через интерфейс не
    // назначается (создаётся сидером/командой при установке коробки)
    public const ASSIGNABLE_ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_MANAGER,
        self::ROLE_DISPATCHER,
        self::ROLE_DRIVER,
        self::ROLE_CLIENT,
    ];

    /**
     * Подписи всех ролей: значение => подпись.
     *
     * @return array<string, string>
     */
    public static function roleLabels(): array
    {
        return self::ROLE_LABELS;
    }

    /**
     * Роли, доступные для назначения в админке: значение => подпись.
     *
     * @return array<string, string>
     */
    public static function assignableRoles(): array
    {
        return array_intersect_key(self::ROLE_LABELS, array_flip(self::ASSIGNABLE_ROLES));
    }

    // Открыт ли пользователю вход в веб-панель
    public function hasPanelAccess(): bool
    {
        return in_array($this->role, self::PANEL_ROLES, true);
    }

    public function isSuperadmin(): bool
    {
        return $this->role === self::ROLE_SUPERADMIN;
    }

    // Заблокирован ли пользователь: вход запрещён и в веб-панель,
    // и в приложения, выданные API-токены перестают работать
    public function isBlocked(): bool
    {
        return (bool) $this->is_blocked;
    }

    // Подпись текущей роли
    public function getRoleLabelAttribute(): ?string
    {
        return self::ROLE_LABELS[$this->role] ?? $this->role;
    }

    /**
     * Кого видит в списке пользователей конкретный сотрудник панели:
     * суперадмин нашей компании не отображается никому, кроме
     * другого суперадмина.
     */
    public function scopeVisibleInAdmin($query, User $viewer)
    {
        if (!$viewer->isSuperadmin()) {
            $query->where('role', '!=', self::ROLE_SUPERADMIN);
        }

        return $query;
    }

   
    // Реализация методов интерфейса JWTSubject
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function unreadMessagesCount()
    {
        return $this->messages()
            ->where('type', 'user')
            ->where('is_active', 0)
            ->count();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'last_name',
        'first_name',
        'email',
        'phone',
        'password',
        'role',
        'current_car_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
        'is_blocked' => 'boolean',
    ];

    protected $with = ['car'];

    public function car()
    {
        return $this->hasOne(Car::class);
    }

    // Автомобиль, к которому привязано устройство, на котором
    // водитель сейчас авторизован: водительское приложение после
    // входа докладывает выбранное при установке авто на сервер
    // (CarBindingService::reportCurrentCar), диспетчер видит его
    // в админке.
    public function currentCar()
    {
        return $this->belongsTo(Car::class, 'current_car_id');
    }

    /**
     * Профиль пользователя для приложений (вход, /auth/me,
     * проверка токена): обычная сериализация модели плюс
     * привязанный автомобиль ключом current_car - только id,
     * марка, модель и госномер; null, когда привязки нет.
     * Исторический ключ car (связь по cars.user_id) не трогаем.
     *
     * @return array<string, mixed>
     */
    public function profilePayload(): array
    {
        // JWT-путь (me) даёт пользователя без загруженных связей:
        // без loadMissing исторический ключ car пропадает из ответа.
        $this->loadMissing('car');

        $data = $this->toArray();

        $car = $this->currentCar;
        $data['current_car'] = $car ? [
            'id' => $car->id,
            'brand' => $car->brand,
            'model' => $car->model,
            'number_plate' => $car->number_plate,
        ] : null;

        return $data;
    }

    public function location()
    {
        return $this->hasOne(DriverLocation::class);
    }


}
