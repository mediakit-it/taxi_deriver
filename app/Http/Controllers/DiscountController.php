<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Discount;

class DiscountController extends Controller
{
    public function getActionAll(Request $request){
         // Получаем аутентифицированного пользователя по токену
         $user = $request->user();
        
         // Если пользователь аутентифицирован, используем его страну для запроса магазинов
         if ($user) {
             $actions = Discount::where('country',$user->country)->get()->sortByDesc('order')->values();
             return response()->json([
                 'status' => 'success',
                 'actions' => $actions,
             ]);
         } else {
             // Если пользователь не аутентифицирован, возвращаем сообщение об ошибке
             return response()->json(['error' => 'Unauthenticated'], 401);
         }
    }
    
}
