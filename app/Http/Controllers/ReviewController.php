<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Review;
use App\Models\Product;


class ReviewController extends Controller
{
    public function newReviews(Request $request){
        // Получаем аутентифицированного пользователя по токену
        $user = Auth::user();

        // Если пользователь аутентифицирован, используем его страну для запроса магазинов
        if ($user) {
            $product = Product::where('slug',$request->input('product_slug'))->first();
        
            $review = Review::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'text' => $request->input('message'),
                'rating' => $request->input('rating'),
                'active' => 1,
            ]);

            $review->load('user');

            return response()->json([
                'status' => 'success',
                'review' => $review,
            ]);
        } else {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
    }
}
