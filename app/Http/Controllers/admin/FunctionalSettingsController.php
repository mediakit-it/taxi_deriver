<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateFunctionalSettingsRequest;
use App\Services\FunctionalSettingsService;
use Illuminate\Http\RedirectResponse;

class FunctionalSettingsController extends Controller
{
    public function __construct(private FunctionalSettingsService $functionalSettings)
    {
    }

    // Сохранить раздел «Функционал» (только суперадмин: роут и
    // FormRequest закрыты для остальных ролей)
    public function update(UpdateFunctionalSettingsRequest $request): RedirectResponse
    {
        $this->functionalSettings->update($request->validated());

        return redirect()->route('settings.edit')
            ->with('success', 'Настройки функционала сохранены');
    }
}
