<?php

namespace App\Http\Requests\Api;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

// Доклад водительского приложения о привязанном к устройству авто
// после авторизации водителя (POST /api/v1/car-binding/report).
// Докладывать может только водитель: остальные роли получают 403
// на уровне authorize(). Существование авто проверяется не правилом
// exists, а в CarBindingService - ответу нужен свой текст ошибки
// («Автомобиль не найден»).
class CarBindingReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === User::ROLE_DRIVER;
    }

    public function rules(): array
    {
        return [
            'car_id' => ['required', 'integer'],
        ];
    }
}
