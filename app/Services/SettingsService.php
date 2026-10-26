<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;

class SettingsService
{
    /**
     * Ключи-массивы: в таблице settings хранятся как JSON-строки.
     *
     * @var array<int, string>
     */
    private array $arrayKeys = ['phones', 'addresses'];

    /**
     * Сохранить набор настроек. Массивы (phones, addresses)
     * кодируются в JSON, полностью пустые строки отбрасываются.
     */
    public function updateScalars(array $data): void
    {
        foreach ($data as $key => $value) {
            if (in_array($key, $this->arrayKeys, true) && is_array($value)) {
                $value = json_encode($this->filterEmptyRows($value), JSON_UNESCAPED_UNICODE);
            }

            Setting::set($key, $value);
        }
    }

    /**
     * Пароль привязки авто к устройству водителя: в настройках хранится
     * только хеш. Пустое значение - пароль не меняется.
     */
    public function updateCarBindPassword(?string $plainPassword): void
    {
        if ($plainPassword === null || $plainPassword === '') {
            return;
        }

        Setting::set('car_bind_password', Hash::make($plainPassword));
    }

    /**
     * Сохранить загруженный файл на диск public в папку settings/<dir>.
     */
    public function storeUploadedFile(UploadedFile $file, string $dir): string
    {
        return $file->store('settings/' . $dir, 'public');
    }

    /**
     * Убрать строки, в которых все поля пустые.
     */
    private function filterEmptyRows(array $rows): array
    {
        $rows = array_filter($rows, function ($row) {
            return is_array($row) && collect($row)->filter()->isNotEmpty();
        });

        return array_values($rows);
    }
}
