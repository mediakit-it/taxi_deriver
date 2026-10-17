<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Product;
use App\Models\Cart;
use App\Models\ShopProduct;
use App\Models\Category;
use App\Models\Basket;
use App\Models\BasketItem;
use App\Models\MethodDelivery;

use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Facades\Log;

class BasketController extends Controller
{

    public function newOrder(Request $request){

       $user = Auth::user();
    
        if ($user) {
            $country = $user->country;

            $delivery = MethodDelivery::find($request->methodDeliveryId);

            $cartsProverka = Cart::where([['user_id', $user->id],['country', $country]])->with('shopProduct.product.brand')->get();
            
            $basket = Basket::create([
                'first_name' => $user->first_name,
                'otchestvo' => $user->otchestvo,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'fullPrice' => $request->fullMoney,
                'discount' => $request->discountMoney,
                'discountUser' => $request->discountUser,
                'deliveryPrice' => $request->moneyDelivery,
                'nameDeliveryRu' => $delivery->name_ru,
                'nameDeliveryKz' => $delivery->name_kz,
                'addressDelivery' => $request->address,
                'is_paid' => 'no_oplat',
                'is_complete' => 'no_dostavka',
                'obrabotka' => 'obrabotka',
                'country' => $country,
            ]);

            $carts = Cart::where([['user_id', $user->id],['country', $country]])->with('shopProduct.product.brand')->get();

            foreach($carts as $cart){
                $shopProduct = ShopProduct::find($cart->shop_product_id);
                if($cart->variable == 'false'){
                    $basket_item = BasketItem::create([
                        'product_name_ru' => $shopProduct->product->name_ru,
                        'product_name_kz' => $shopProduct->product->name_kz,
                        'article' =>$shopProduct->product->article,
                        'nameBrandRu' => $shopProduct->product->brand->name_ru,
                        'nameBrandKz' => $shopProduct->product->brand->name_kz,
                        'price' => $shopProduct->price,
                        'imgUrl'=>$shopProduct->product->gallery[0],
                        'discount' => $shopProduct->discounted_price,
                        'discount_user' => $shopProduct->discounted_price>0 ? 0 : $shopProduct->price * ($user->discountUser / 100),
                        'shopMagazine' =>$shopProduct->shop->toArray(),
                        'quantity' => $cart->quantity,
                        'basket_id' => $basket->id,
                    ]);

                    if($shopProduct->quantity - $cart->quantity >= 0){
                        $shopProduct->quantity = $shopProduct->quantity - $cart->quantity;
                        $shopProduct->save();
                    }
                }else{
                    $variant = ShopProduct::find($cart->shop_product_id)->variations;
                    $yourCriteria = $cart->variations;

                    // Предположим, что $variant - это массив вариаций
                    $filteredVariants = array_filter($variant, function($item) use ($yourCriteria) {
                        // Здесь $yourCriteria - это ваш критерий, который определяет, какой элемент вы хотите получить
                        return $item['name_kz'] == $yourCriteria['name_kz'] && $item['name_ru'] == $yourCriteria['name_ru'] && $item['nameAttribute_kz'] == $yourCriteria['nameAttribute_kz'] && $item['nameAttribute_ru'] == $yourCriteria['nameAttribute_ru'];
                    });
                    $firstMatch = reset($filteredVariants);

                    $basket_item = BasketItem::create([
                        'product_name_ru' => $shopProduct->product->name_ru,
                        'product_name_kz' => $shopProduct->product->name_kz,
                        'article' =>$shopProduct->product->article,
                        'nameBrandRu' => $shopProduct->product->brand->name_ru,
                        'nameBrandKz' => $shopProduct->product->brand->name_kz,
                        'price' => $firstMatch['price'],
                        'imgUrl'=>$shopProduct->product->gallery[0],
                        'discount' => $firstMatch['priceDiscount'],
                        'discount_user' => $firstMatch['priceDiscount'] > 0 ? 0 : $firstMatch['price'] * ($user->discountUser / 100),
                        'shopMagazine' =>$shopProduct->shop->toArray(),
                        'quantity' => $cart->quantity,
                        'basket_id' => $basket->id,
                        'isVariation' => true,
                        'nameAttributeRu' => $firstMatch['nameAttribute_ru'],
                        'nameAttributeKz' => $firstMatch['nameAttribute_kz'],
                        'nameVariationRu' => $firstMatch['name_ru'],
                        'nameVariationKz' => $firstMatch['name_kz'],
                    ]);

                    if ($firstMatch['quantity'] - $cart->quantity >= 0) {
                        $variant = collect($variant)->map(function ($item) use ($firstMatch, $cart) {
                            if ($item['name_kz'] == $firstMatch['name_kz'] && 
                                $item['name_ru'] == $firstMatch['name_ru'] && 
                                $item['nameAttribute_kz'] == $firstMatch['nameAttribute_kz'] && 
                                $item['nameAttribute_ru'] == $firstMatch['nameAttribute_ru']) {
                                    $item['quantity'] -= $cart->quantity;
                            }
                            return $item;
                        })->toArray();
                    
                        // Обновление значения в базе данных
                        ShopProduct::find($cart->shop_product_id)->update([
                            'variations' => $variant,
                        ]);
                    }
                }
            }

            Cart::where([['user_id', $user->id],['country', $country]])->delete();

            try {
                Mail::send('mails.zakaz', ['basket' => $basket], function($mailMessage) {
                    $mailMessage->to(env('EMAIL_ADMIN'))->subject('Новый заказ');
                });
            } catch (Exception $e) {
                Log::error('почта не работает -- ' . $e->getMessage());
            }

            return response()->json([
                'success' => 'true',
            ]);

        } else {
            // Если пользователь не аутентифицирован, возвращаем сообщение об ошибке
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
    }

    public function getOrdersUser(Request $request){
        $user = Auth::user();
    
        if ($user) {
            $country = $user->country;

            $baskets = Basket::where('email',$user->email)->with('basketitems')->orderByDesc('id')->get();

            return response()->json([
                'success' => 'true',
                'baskets' => $baskets,
            ]);

        } else {
            // Если пользователь не аутентифицирован, возвращаем сообщение об ошибке
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
    }
}
