<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Category;

class CategoryController extends Controller
{
    public function index(Request $request){
        // Получаем аутентифицированного пользователя по токену
        $user = $request->user();
        
        // Если пользователь аутентифицирован, используем его страну для запроса магазинов
        if ($user) {
            $categories = Category::where('parent_id', null)->orderByDesc('order')->get();
            return response()->json([
                'status' => 'success',
                'categories' => $categories,
            ]);
        } else {
            // Если пользователь не аутентифицирован, возвращаем сообщение об ошибке
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
    }
}
