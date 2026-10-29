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
            // Водитель определяется по токену, а не по user_id из тела
            // запроса: приложение может прислать 0 (профиль ещё не
            // сохранён локально) или устаревший id, и пинг тогда уходил
            // в никуда - диспетчер не видел машину на карте.
            $userId = $request->user()?->id ?? $request->input('user_id');

            DriverLocation::updateOrCreate(
                ['user_id' => $userId],
                [
                    'lat' => $request->input('lat'),
                    'lng' => $request->input('lng'),
                    'is_zakaz' => $request->input('isZakaz'),
                    'order_id' => $request->input('orderId'),
                ]
            );
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            // Просто возвращаем ошибку, чтобы не сломать клиента
            return response()->json(['status' => 'failed'], 500);
        }
    }

}
