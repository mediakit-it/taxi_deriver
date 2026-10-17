<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Whishlist;
use App\Models\Product;
use App\Models\ShopProduct;

class WishlistController extends Controller
{

    public function getWishlistUser(Request $request){
         // Получаем аутентифицированного пользователя по токену
         $user = $request->user();
         if ($user) {

            $wishlistId = Whishlist::where('user_id',$user->id)->get();
            

            // // Получаем соответствующие продукты для выбранных product_id
            $products = Product::whereIn('id', $wishlistId->pluck('product_id'))
                ->with(['shopProducts','brand','category','metkas'])
                ->get();
            // // $products = Product::where('country', $country)->take(10)->get();

             // Проверяем, есть ли продукт в списке желаний пользователя
            $products->each(function ($product) use ($user) {
                $product->is_in_wishlist = $user->whishlists()->where('product_id', $product->id)->exists();
            });

            return response()->json([
                'status' => 'success',
                'products' => $products->makeHidden(['category_id', 'brand_id', 'created_at', 'updated_at']),
            ]);
         } else {
            // Если пользователь не аутентифицирован, возвращаем сообщение об ошибке
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
    }

    public function updateWishlistProduct(Request $request){
        
        // Получаем аутентифицированного пользователя по токену
        $user = $request->user();
        
        // Если пользователь аутентифицирован, используем его страну для запроса магазинов
        if ($user) {
            if(Product::find($request->product_id)->exists()){
                if(Whishlist::where([['user_id',$user->id],['product_id',$request->product_id]])->exists()){
                    Whishlist::where([['user_id',$user->id],['product_id',$request->product_id]])->delete();
                    return response()->json([
                        'success' => true,
                        'product_id' => $request->product_id,
                        'status' => false // Возвращаем пользователя вместе с ответом
                    ]);

                }else{
                    Whishlist::create([
                        'user_id'=>$user->id,
                        'product_id'=>$request->product_id,
                    ]);

                    return response()->json([
                        'success' => true,
                        'product_id' => $request->product_id,
                        'status' => true // Возвращаем пользователя вместе с ответом
                    ]);
                }
            }else{
                return response()->json([
                    'success' => false,
                    'product_id' => $request->product_id,
                    'status' => false // Возвращаем пользователя вместе с ответом
                ]);
            }
        } else {
            // Если пользователь не аутентифицирован, возвращаем сообщение об ошибке
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
    }


}
