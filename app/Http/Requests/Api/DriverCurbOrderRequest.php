<?php

namespace App\Http\Requests\Api;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

// Создание заказа «от борта» водительским приложением
// (POST /api/v1/driver/orders): пассажир сел с улицы, водитель сам
// заводит заказ. Создавать может только водитель - остальные роли
// получают 403 на уровне authorize(), как в CarBindingReportRequest.
// Деловые условия (привязанное авто, точка подачи) проверяет
// контроллер: ответам нужны свои тексты ошибок.
class DriverCurbOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === User::ROLE_DRIVER;
    }

    public function rules(): array
    {
        return [
            'to_lat' => ['required', 'numeric', 'between:-90,90'],
            'to_lng' => ['required', 'numeric', 'between:-180,180'],
            'to_address' => ['required', 'string', 'max:255'],
            'from_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'from_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'from_address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'comment' => ['nullable', 'string'],
            'distance' => ['nullable', 'integer', 'min:0'],
            'duration' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
