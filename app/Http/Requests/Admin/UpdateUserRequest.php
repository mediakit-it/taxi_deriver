<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Правила редактирования пользователя через админку. Роль -
     * только из назначаемых: назначить superadmin нельзя.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'last_name' => 'nullable|string|max:255',
            'first_name' => 'nullable|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->route('user')),
            ],
            'phone' => 'nullable|string|max:64',
            'role' => ['required', Rule::in(User::ASSIGNABLE_ROLES)],
            'current_car_id' => 'nullable|integer|exists:cars,id',
        ];
    }
}
