<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderPriceRequest extends FormRequest
{
    /**
     * Доступ сюда закрыт маршрутом (роли панели), отдельной
     * авторизации у запроса нет.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Быстрая правка цены из списка заказов: цена обязательна,
     * число, не отрицательная.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'price' => ['required', 'numeric', 'min:0'],
        ];
    }
}
