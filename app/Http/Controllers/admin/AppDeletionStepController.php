<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAppDeletionStepRequest;
use App\Http\Requests\Admin\UpdateAppDeletionStepRequest;
use App\Models\AppDeletionStep;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class AppDeletionStepController extends Controller
{
    public function __construct(private SettingsService $settingsService)
    {
    }

    // Добавить шаг на страницу «Как удалить приложение»
    public function store(StoreAppDeletionStepRequest $request): RedirectResponse
    {
        $data = $request->validated();
        unset($data['image']);

        if ($request->hasFile('image')) {
            $data['image_path'] = $this->settingsService->storeUploadedFile($request->file('image'), 'deletion-steps');
        }

        AppDeletionStep::create($data);

        return back()->with('success', 'Шаг добавлен');
    }

    // Обновить шаг: текст, порядок и картинку (с заменой файла)
    public function update(UpdateAppDeletionStepRequest $request, AppDeletionStep $step): RedirectResponse
    {
        $data = $request->validated();
        unset($data['image']);

        if ($request->hasFile('image')) {
            if ($step->image_path) {
                Storage::disk('public')->delete($step->image_path);
            }

            $data['image_path'] = $this->settingsService->storeUploadedFile($request->file('image'), 'deletion-steps');
        }

        $step->update($data);

        return back()->with('success', 'Шаг обновлён');
    }

    // Удалить шаг вместе с картинкой
    public function destroy(AppDeletionStep $step): RedirectResponse
    {
        if ($step->image_path) {
            Storage::disk('public')->delete($step->image_path);
        }

        $step->delete();

        return back()->with('success', 'Шаг удалён');
    }
}
