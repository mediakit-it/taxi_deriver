<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Validator;

use App\Models\Importproduct;

use App\Jobs\ImportFile;

class ImportController extends Controller
{
    public function index(){
        $importProducts = Importproduct::all()->sortByDesc('id');
        return view('admin.import.show',compact('importProducts'));
    }

    public function importProduct(Request $request){
        // dd($request->file);
        ini_set("memory_limit","-1"); 
        $validator = Validator::make(
            [
                'file' => $request->file,
            ],
            [
                'file' => 'required|file', 
            ],
            [
                'required' => 'Поле :attribute обязательно для заполнения.',
                'file' => 'Поле :attribute должно быть файлом.',
            ]
        );
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors());
        }
        
        if ($request->hasFile('file')) {
            $validator = Validator::make(
                [
                    'extension' => strtolower($request->file('file')->getClientOriginalExtension()),
                ],
                [
                    'extension' => 'required|in:xlsx',
                ],
                [
                    'required' => 'Поле файл обязательно для заполнения.',
                    'in' => 'Выбранное расширение для файла некорректно, должно быть расширение xlsx.',
                ]
            );
        
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator->errors());
            }
        } else {
            return redirect()->back()->withErrors(['file' => 'Файл не загружен.']);
        }
        

        if ($request->hasFile('file')) { 
            $fileName = $request->file->getClientOriginalName(); 
            $request->file->move(public_path('uploads/import'), $fileName); 
        } 


        try { 
            $import = Importproduct::create([
                'name'=>$fileName,
                'status'=>'Импорт начался',
                'error'=>'Ошибок нет',
            ]);

            ImportFile::dispatch($fileName,$import)->onConnection('database')->onQueue('imports');
        // $this->productImport->setFileName($fileName); 
        // RunImport::dispatch($this->productImport); 
            return redirect(route('import'))->with('status', 'Импорт начался'); 
        } catch (\Exception $e) {  
            return redirect(route('import'))->with('status', $e->getMessage()); 
        }

        

        // return back();
    }

    // public function importProductUpdate(Request $request){
    //     // dd($request->file);
    //     ini_set("memory_limit","-1"); 
    //     $validator = Validator::make(
    //         [
    //             'file' => $request->file,
    //         ],
    //         [
    //             'file' => 'required|file', 
    //         ],
    //         [
    //             'required' => 'Поле выбрать файл обязательно для заполнения.',
    //             'file' => 'Поле выбрать файл должно быть файлом.',
    //         ]
    //     );
        
    //     if ($validator->fails()) {
    //         return redirect()->back()->withErrors($validator->errors());
    //     }
        
    //     if ($request->hasFile('file')) {
    //         $validator = Validator::make(
    //             [
    //                 'extension' => strtolower($request->file('file')->getClientOriginalExtension()),
    //             ],
    //             [
    //                 'extension' => 'required|in:xlsx',
    //             ],
    //             [
    //                 'required' => 'Поле выбрать файл обязательно для заполнения.',
    //                 'in' => 'Выбранное расширение для файла некорректно, должно быть расширение xlsx.',
    //             ]
    //         );
        
    //         if ($validator->fails()) {
    //             return redirect()->back()->withErrors($validator->errors());
    //         }
    //     } else {
    //         return redirect()->back()->withErrors(['file' => 'Файл не загружен.']);
    //     }
        

    //     if ($request->hasFile('file')) { 
    //         $fileName = $request->file->getClientOriginalName(); 
    //         $request->file->move(public_path('uploads/import'), $fileName); 
    //     } 


    //     try { 
    //         $import = Importproduct::create([
    //             'name'=>$fileName,
    //             'status'=>'Обновление цен началось',
    //             'error'=>'Ошибок нет',
    //         ]);

    //         ImportFileUpdate::dispatch($fileName,$import);
    //         return redirect(backpack_url('importProduct'))->with('status', 'Обновление цен началось'); 
    //     } catch (\Exception $e) {  
    //         $import = Importproduct::create([
    //             'name'=>$fileName,
    //             'status'=>'Обновление цен не началось',
    //             'error'=>$e->getMessage(),
    //         ]);
    //         return redirect(route('import'))->with('status', $e->getMessage()); 
    //     }

        

    //     return back();
    // }
}
