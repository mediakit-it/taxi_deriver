<?php

namespace App\Http\Requests\Api;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

// Отпускание принятого заказа водительским приложением
// (POST /api/v1/driver/orders/release): водитель уже едет к
// клиенту, но вынужден отказаться - заказ возвращается в поиск
// другого водителя. Отпускать может только водитель: остальные
// роли получают 403 на уровне authorize(), как в
// DriverCurbOrderRequest. Принадлежность заказа и его статус
// проверяет контроллер: ответам нужны свои тексты ошибок и коды
// (403 - чужой заказ, 422 - отпускать уже поздно).
class DriverReleaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === User::ROLE_DRIVER;
    }

    public function rules(): array
    {
        return [
            'order_id' => ['required', 'integer'],
        ];
    }
}
