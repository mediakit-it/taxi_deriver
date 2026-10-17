<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShopRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name_ru' => 'required',
            'name_kz' => 'required',
            'city_ru' => 'required',
            'city_kz' => 'required',
            'address_ru' => 'required',
            'address_kz' => 'required',
            'phone' => 'required',
            'country' => 'required',
        ];
    }
}
