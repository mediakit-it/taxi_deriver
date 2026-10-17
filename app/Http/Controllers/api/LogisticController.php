<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\DriverLocation;



class LogisticController extends Controller
{
    public function update(Request $request)
    {
        try {
            DriverLocation::updateOrCreate(
                ['user_id' => $request->input('user_id')],
                [
                    'lat' => $request->input('lat'),
                    'lng' => $request->input('lng'),
                    'is_zakaz' => $request->input('isZakaz'),
                    'order_id' => $request->input('orderId'),
                ]
            );
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            // Можно записать ошибку в лог, если нужно:
      
    
            // Просто возвращаем успех, чтобы не сломать клиента
            return response()->json(['status' => 'failed'], 500);
        }
    }

}
