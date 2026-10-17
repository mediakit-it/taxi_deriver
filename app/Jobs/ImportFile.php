<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\Importproduct;

use App\Imports\ProductImport;
use Maatwebsite\Excel\Facades\Excel;

use Illuminate\Support\Facades\Log;

class ImportFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $import;
    protected $fileName;

    /**
     * Create a new job instance.
     */
    public function __construct($fileName,Importproduct $import)
    {
        $this->import = $import;
        $this->fileName = $fileName;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try { 

            Excel::import(new ProductImport, public_path('uploads/import/'.$this->fileName));

            $this->import->update([
                'status'=>'Файлы успешно импортированы',
            ]);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {  
            $failures = $e->failures();
    
            foreach ($failures as $failure) {
                Log::error('Ошибка при импорте файла: ', [
                    'row' => $failure->row(), // номер строки
                    'attribute' => $failure->attribute(), // столбец
                    'errors' => $failure->errors(), // ошибки
                    'values' => $failure->values(), // данные строки
                ]);
            }
            
            $this->import->update([
                'status' => 'Файлы небыли импортированы',
                'error' => $e->getMessage(),
            ]);
        } catch (\Exception $e) {
            Log::error('Общая ошибка при импорте файла: ' . $e->getMessage());
        
            $this->import->update([
                'status' => 'Файлы небыли импортированы',
                'error' => $e->getMessage(),
            ]);
        }
        
    }
}
