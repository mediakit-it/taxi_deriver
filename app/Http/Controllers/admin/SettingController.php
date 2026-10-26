<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Models\AppDeletionStep;
use App\Models\PolicyDocument;
use App\Models\Setting;
use App\Services\FunctionalSettingsService;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function __construct(
        private SettingsService $settingsService,
        private FunctionalSettingsService $functionalSettings,
    ) {
    }

    // Показать страницу настроек: скаляры, документы и шаги удаления приложения
    public function edit(): View
    {
        $settings = Setting::allCached();
        $documents = PolicyDocument::ordered()->get();
        $steps = AppDeletionStep::ordered()->get();
        $functional = $this->functionalSettings->all();

        return view('admin.settings.edit', compact('settings', 'documents', 'steps', 'functional'));
    }

    // Сохранить скалярные настройки и загруженные файлы оформления
    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        $data = $request->validated();
        unset($data['logo'], $data['favicon'], $data['seo_og_image']);

        $this->settingsService->updateScalars($data);

        if ($request->hasFile('logo')) {
            Setting::set('logo_path', $this->settingsService->storeUploadedFile($request->file('logo'), 'branding'));
        }

        if ($request->hasFile('favicon')) {
            Setting::set('favicon_path', $this->settingsService->storeUploadedFile($request->file('favicon'), 'branding'));
        }

        if ($request->hasFile('seo_og_image')) {
            Setting::set('seo_og_image_path', $this->settingsService->storeUploadedFile($request->file('seo_og_image'), 'seo'));
        }

        return redirect()->route('settings.edit')
            ->with('success', 'Настройки сохранены');
    }
}
