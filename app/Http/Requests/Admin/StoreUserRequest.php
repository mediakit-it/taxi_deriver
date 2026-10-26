<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Правила создания пользователя через админку. Роль - только из
     * назначаемых: superadmin через интерфейс не создаётся.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'last_name' => 'nullable|string|max:255',
            'first_name' => 'nullable|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:64',
            'password' => 'required|string|max:255',
            'role' => ['required', Rule::in(User::ASSIGNABLE_ROLES)],
        ];
    }
}
