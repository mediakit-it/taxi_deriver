<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Imports\ProductImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\File;

class CheckAndImportFiles extends Command
{
    protected $signature = 'import:check-file';
    protected $description = 'Check for the file "oborotka.xlsx" and import if updated since last import';

    public function handle()
    {
        $directory = public_path('uploads/import');
        $fileName = 'oborotka.xlsx';
        $filePath = $directory . '/' . $fileName;
        $importTimeFile = storage_path('app/imports/oborotka_import_time.txt');

        // Проверяем, существует ли файл
        if (!File::exists($filePath)) {
            Log::info("Файл $fileName не найден в папке.");
            return Command::SUCCESS;
        }

        // Время последнего изменения файла
        $fileModifiedTime = filemtime($filePath);

        // Считываем время последнего успешного импорта из файла
        $lastImportTime = 0;
        if (File::exists($importTimeFile)) {
            $lastImportTime = (int)File::get($importTimeFile);
        }

        // Если файл был изменен после последнего импорта
        if ($fileModifiedTime > $lastImportTime) {
            Log::info("Файл $fileName был обновлен. Начинаем импорт.");

            try {
                // Импортируем файл
                Excel::import(new ProductImport, $filePath);

                // Обновляем файл с временем импорта
                File::put($importTimeFile, $fileModifiedTime);

                Log::info("Файл $fileName успешно импортирован.");
            } catch (\Exception $e) {
                Log::error("Ошибка при импорте файла $fileName: " . $e->getMessage());
            }
        } else {
            Log::info("Файл $fileName не изменялся с последнего импорта.");
        }

        return Command::SUCCESS;
    }
}
