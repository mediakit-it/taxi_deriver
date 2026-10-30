<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCarRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Изменение автомобиля в админке. driver_id - водитель, к
     * устройству которого автомобиль привязывается
     * (users.current_car_id); пустое значение снимает привязку.
     * photo - новая фотография взамен прежней.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'brand' => 'required|string|max:255',
            'model' => 'nullable|string|max:255',
            'number_plate' => 'required|string|max:255',
            'driver_id' => 'nullable|integer|exists:users,id',
            'photo' => 'nullable|image|max:5120',
        ];
    }
}
