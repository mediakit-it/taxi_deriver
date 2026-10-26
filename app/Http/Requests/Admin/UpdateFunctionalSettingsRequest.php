<?php

namespace App\Http\Requests\Admin;

use App\Services\FunctionalSettingsService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFunctionalSettingsRequest extends FormRequest
{
    /**
     * Раздел «Функционал» редактирует только суперадмин нашей
     * компании (дополнительно к route-middleware role:superadmin).
     */
    public function authorize(): bool
    {
        $user = $this->user('web');

        return $user !== null && $user->isSuperadmin();
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'functional_price_mode' => ['required', Rule::in(FunctionalSettingsService::PRICE_MODES)],
            'functional_assignment_mode' => ['required', Rule::in(FunctionalSettingsService::ASSIGNMENT_MODES)],
            'functional_order_sources' => ['required', Rule::in(FunctionalSettingsService::ORDER_SOURCES_MODES)],
            'functional_preorders_enabled' => 'nullable|boolean',
            'functional_ratings_enabled' => 'nullable|boolean',
        ];
    }
}
