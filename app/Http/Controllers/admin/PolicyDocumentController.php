<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePolicyDocumentRequest;
use App\Models\PolicyDocument;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class PolicyDocumentController extends Controller
{
    public function __construct(private SettingsService $settingsService)
    {
    }

    // Добавить документ (политики, договоры): PDF-файл и название
    public function store(StorePolicyDocumentRequest $request): RedirectResponse
    {
        $path = $this->settingsService->storeUploadedFile($request->file('file'), 'documents');

        PolicyDocument::create([
            'title' => $request->validated()['title'],
            'file_path' => $path,
        ]);

        return back()->with('success', 'Документ добавлен');
    }

    // Удалить документ вместе с файлом
    public function destroy(PolicyDocument $document): RedirectResponse
    {
        Storage::disk('public')->delete($document->file_path);

        $document->delete();

        return back()->with('success', 'Документ удалён');
    }
}
