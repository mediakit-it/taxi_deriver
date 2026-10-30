<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AssignOrderDriverRequest extends FormRequest
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
     * Пустая строка из селекта «Не назначен» - это снятие водителя,
     * приводим её к null до валидации.
     */
    protected function prepareForValidation(): void
    {
        if ($this->input('driver_id') === '') {
            $this->merge(['driver_id' => null]);
        }
    }

    /**
     * Быстрое назначение водителя из списка заказов: водитель либо
     * выбран из существующих пользователей, либо снят (null).
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'driver_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
