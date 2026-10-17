<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Baner;

class BanerController extends Controller
{
    public function getBanners(){
        $user = Auth::user();
    
        // Если пользователь аутентифицирован, используем его страну для запроса магазинов
        if ($user) {
            $country = $user->country;
            $baners = Baner::where('country',$country)->get();

            return response()->json([
                'status' => 'success',
                'baners' => $baners,
            ]);
        } else {
            // Если пользователь не аутентифицирован, возвращаем сообщение об ошибке
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
    }
}
