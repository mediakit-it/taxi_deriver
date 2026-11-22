<?php

namespace App\Http\Requests\Api;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Отмена заказа в поездке водительским приложением
// (POST /api/v1/driver/orders/cancel): пассажир уже в машине, но
// поездка срывается - водитель обязан назвать причину и оставить
// комментарий, без них диспетчер не понимает, что случилось с
// заказом. Отменять может только водитель: остальные роли получают
// 403 на уровне authorize(), как в DriverReleaseOrderRequest.
// Принадлежность заказа и его статус проверяет контроллер:
// ответам нужны свои тексты ошибок и коды (403 - чужой заказ,
// 422 - заказ ещё не в поездке).
class DriverCancelOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === User::ROLE_DRIVER;
    }

    public function rules(): array
    {
        return [
            'order_id' => ['required', 'integer'],
            'reason' => ['required', Rule::in(array_keys(Order::CANCEL_REASON_LABELS))],
            'comment' => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'Укажите причину отмены заказа.',
            'reason.in' => 'Недопустимая причина отмены заказа.',
            'comment.required' => 'Добавьте комментарий к отмене заказа.',
        ];
    }
}
