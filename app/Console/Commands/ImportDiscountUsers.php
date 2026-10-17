<?php

namespace App\Console\Commands;

use App\Imports\DiscountUsersImport;
use App\Imports\ProductImport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\Console\Command\Command as CommandAlias;

class ImportDiscountUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:discount-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import discounts users from file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $directory = public_path('uploads/import');
        $fileName = 'imporDiscountUser.xlsx';
        $filePath = $directory . '/' . $fileName;
        $importTimeFile = storage_path('app/imports/discount_import_time.txt');

        // Проверяем, существует ли файл
        if (!File::exists($filePath)) {
            Log::info("Файл $fileName не найден в папке.");
            return CommandAlias::FAILURE;
        }

        $fileModifiedTime = filemtime($filePath);

        $lastImportTime = 0;
        if (File::exists($importTimeFile)) {
            $lastImportTime = (int)File::get($importTimeFile);
        }

        if ($fileModifiedTime > $lastImportTime) {
            Log::info("Файл $fileName был обновлен. Начинаем импорт.");

            try {
                // Импортируем файл
                Excel::import(new DiscountUsersImport, $filePath);

                // Обновляем файл с временем импорта
                File::put($importTimeFile, $fileModifiedTime);

                Log::info("Файл $fileName успешно импортирован.");
            } catch (\Exception $e) {
                Log::error("Ошибка при импорте файла $fileName: " . $e->getMessage());
            }
        } else {
            Log::info("Файл $fileName не изменялся с последнего импорта.");
        }

        return CommandAlias::SUCCESS;
    }
}
