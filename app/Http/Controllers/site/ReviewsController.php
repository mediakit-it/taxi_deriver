<?php

namespace App\Http\Controllers\site;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use App\Models\Product;
use App\Models\Review;

class ReviewsController extends Controller
{
    public function newReviews(Request $request){
            // Получаем аутентифицированного пользователя по токену
            $user = Auth::guard('web')->user();
            $product = Product::where('slug',$request->product_slug)->first();

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
                'product_rating' => number_format($product->reviews()->avg('rating'), 1),
            ]);
    }
}
