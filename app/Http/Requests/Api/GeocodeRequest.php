<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class GeocodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'address' => ['required', 'string', 'max:255'],
            // Optional: > 1 switches the response to a list of up to
            // `limit` points (destination search suggestions).
            'limit' => ['nullable', 'integer', 'min:1', 'max:5'],
        ];
    }
}
