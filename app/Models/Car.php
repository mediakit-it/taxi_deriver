<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'brand',
        'model',
        'number_plate',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Подпись автомобиля для списков и выбора при привязке: «Skoda Octavia»
    public function getLabelAttribute(): string
    {
        return trim($this->brand . ' ' . ($this->model ?? ''));
    }
}
