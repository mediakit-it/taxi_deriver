<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Car extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'brand',
        'model',
        'number_plate',
        'photo',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Водитель, к устройству которого привязан автомобиль
    // (users.current_car_id). Это актуальная привязка: её пишет
    // водительское приложение после входа и админка. Связь user()
    // выше - историческая (cars.user_id), больше не поддерживается.
    public function boundDriver()
    {
        return $this->hasOne(User::class, 'current_car_id');
    }

    // Подпись автомобиля для списков и выбора при привязке: «Skoda Octavia»
    public function getLabelAttribute(): string
    {
        return trim($this->brand . ' ' . ($this->model ?? ''));
    }

    // Публичный URL фотографии автомобиля (диск public), если загружена
    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? Storage::url($this->photo) : null;
    }
}
