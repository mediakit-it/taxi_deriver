<?php

namespace App\Http\Controllers\site;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

use App\Models\Whishlist;
use App\Models\Product;
use App\Models\ShopProduct;


class WishlistController extends Controller
{
    public function index(){

            $user = Auth::guard('web')->user();

            $wishlistId = Whishlist::where('user_id',$user->id)->get();
            

            // // Получаем соответствующие продукты для выбранных product_id
            $products = Product::whereIn('id', $wishlistId->pluck('product_id'))
                ->with(['shopProductsWeb','brand','category','metkas'])
                ->get();

       
        return view('site.wishlist.index',['products'=>$products->makeHidden(['category_id', 'brand_id', 'created_at', 'updated_at'])]);
    }

    public function updateWishlistProduct(Request $request){
        
        // Получаем аутентифицированного пользователя по токену
        $user = Auth::guard('web')->user();
        
        if(Product::find($request->product_id)->exists()){
            if(Whishlist::where([['user_id',$user->id],['product_id',$request->product_id]])->exists()){
                Whishlist::where([['user_id',$user->id],['product_id',$request->product_id]])->delete();
                return response()->json([
                    'success' => true,
                ]);

            }else{
                Whishlist::create([
                    'user_id'=>$user->id,
                    'product_id'=>$request->product_id,
                ]);

                return response()->json([
                    'success' => true,
                ]);
            }
        }else{
            return response()->json([
                'success' => false,
            ]);
        }
    }
}
