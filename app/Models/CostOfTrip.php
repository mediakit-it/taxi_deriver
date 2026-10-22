<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CostOfTrip extends Model  // ← было LocationPrice, исправили на CostOfTrip
{
    use HasFactory;

    protected $table = 'cost_of_trip';

    protected $fillable = [
        'name',
        'display_name',
        'price'
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    /**
     * display_name - поле админки и коробки: в публичный API его не отдаём,
     * чтобы набор ключей ответа /api/v1/cost-of-trip остался как раньше.
     */
    protected $hidden = [
        'display_name',
    ];

    /**
     * Человекочитаемое имя класса обслуживания: display_name, если задан,
     * иначе машинное имя (City, Outside_the_City).
     */
    public function getLabelAttribute(): string
    {
        return $this->display_name ?: $this->name;
    }

    /**
     * Цена в legacy-формате, который ждёт мобильное приложение: строка с
     * запятой и без копеек у целых значений - '8', '1,70', - ровно как
     * раньше отдавалась сырая строка из БД.
     */
    public static function formatPrice($value): string
    {
        $formatted = number_format((float) $value, 2, ',', '');

        return str_ends_with($formatted, ',00')
            ? substr($formatted, 0, -3)
            : $formatted;
    }

    /**
     * Сериализация модели для API: price - строка в legacy-формате выше.
     * Внутри приложения (админка, расчёты) price остаётся decimal-строкой
     * вида '1.70' через свойство модели - меняется только JSON-вывод.
     */
    public function attributesToArray(): array
    {
        $attributes = parent::attributesToArray();

        if (array_key_exists('price', $attributes) && $attributes['price'] !== null) {
            $attributes['price'] = self::formatPrice($attributes['price']);
        }

        return $attributes;
    }
}
