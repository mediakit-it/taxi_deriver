<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
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
            'company_name' => 'required|string|max:255',
            'city' => 'nullable|string|max:255',
            'timezone' => 'nullable|string|max:64',
            'currency_label' => 'nullable|string|max:32',
            'notification_email' => 'nullable|email|max:255',
            'driver_accept_seconds' => 'nullable|integer|min:10|max:600',
            'car_bind_password' => 'nullable|string|max:255',

            'color_primary' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'color_accent' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'logo' => 'nullable|image|max:2048',
            'favicon' => 'nullable|image|mimes:ico,png|max:2048',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
            'seo_og_image' => 'nullable|image|max:2048',

            'legal_name' => 'nullable|string|max:255',
            'legal_unp' => 'nullable|string|max:32',
            'legal_address' => 'nullable|string|max:255',
            'legal_bank' => 'nullable|string|max:255',
            'legal_iban' => 'nullable|string|max:64',
            'legal_director' => 'nullable|string|max:255',

            'social_telegram' => 'nullable|string|max:255',
            'social_viber' => 'nullable|string|max:255',
            'social_whatsapp' => 'nullable|string|max:255',
            'social_instagram' => 'nullable|string|max:255',

            'phones' => 'nullable|array',
            'phones.*.number' => 'nullable|string|max:64',
            'phones.*.label' => 'nullable|string|max:128',

            'addresses' => 'nullable|array',
            'addresses.*.address' => 'nullable|string|max:255',
            'addresses.*.label' => 'nullable|string|max:128',
        ];
    }
}
