<?php

namespace App\Http\Controllers\site;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Facades\Log;

use App\Models\Product;
use App\Models\Cart;
use App\Models\ShopProduct;
use App\Models\Category;
use App\Models\Basket;
use App\Models\BasketItem;
use App\Models\MethodDelivery;

class BasketController extends Controller
{
    public function newOrder(Request $request){

        $user = Auth::guard('web')->user();
     
         if ($user) {

            // return response()->json([
            //     'success' => 'false',
            // ]);
            
            $country = $user->country;

            $delivery = MethodDelivery::find($request->methodDelivery);
            $deliveryPrice = 0;
            if($delivery->isMoney=='true'){
                if(get_cart_items_all_maney()<$delivery->otPrice){
                    $deliveryPrice = $delivery->price;
                }
            }

            $addressDelivery = null;
            if($delivery->isAddressUser == 'true'){
                $addressDelivery = [
                    "id"=>0,
                    "user_id"=>$user->id,
                    "first_name" => $request->firstname,
                    "name" => $request->lastname,
                    "city" => $request->city,
                    "flat" => $request->flat,
                    "home" => $request->home,
                    "phone" => $request->phone,
                    "corpus" => $request->corpus,
                    "region" => $request->region,
                    "street" => $request->street,
                    "country" => $country,
                    "postCode" => $request->postCode,
                ];
            }

            // 'first_name' => $request->firstname,
            // 'name' => $request->lastname,
            // 'email' => $request->email,
            // 'phone' => $request->phone,

             $basket = Basket::create([
                'first_name' => $user->first_name,
                'name' => $user->name,
                'otchestvo' => $user->otchestvo,
                'email' => $user->email,
                'phone' => $user->phone,
                'fullPrice' => get_cart_items_all_maney() + $deliveryPrice,
                'discount' => get_cart_items_all_discount(),
                'discountUser' => get_cart_items_all_discount_users(),
                'deliveryPrice' => $deliveryPrice,
                'nameDeliveryRu' => $delivery->name_ru,
                'nameDeliveryKz' => $delivery->name_kz,
                'addressDelivery' => $addressDelivery,
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
             
             $htmlCartItems = view('site.include.cart.cartItemsAuth')->render();
             $htmlSuccessView = view('site.cart.success_order_placing',['basket'=>$basket])->render();

            try {
                Mail::send('mails.zakaz', ['basket' => $basket], function($mailMessage) {
                    $mailMessage->to(env('EMAIL_ADMIN'))->subject('Новый заказ');
                });
            } catch (Exception $e) {
                Log::error('почта не работает -- ' . $e->getMessage());
            }

             return response()->json([
                'success' => $basket,
                'countCartItems'=>get_count_cart(),
                'htmlCartItems'=>$htmlCartItems,
                'htmlSuccessView'=>$htmlSuccessView,
            ]);
 
         } else {
            $country = get_country_from_cookie();
            $delivery = MethodDelivery::find($request->methodDelivery);
            $deliveryPrice = 0;
            if($delivery->isMoney=='true'){
                if(get_cart_items_all_maney()<$delivery->otPrice){
                    $deliveryPrice = $delivery->price;
                }
            }

            $addressDelivery = null;
            if($delivery->isAddressUser == 'true'){
                $addressDelivery = [
                    "id"=>0,
                    "user_id"=>0,
                    "first_name" => $request->firstname,
                    "name" => $request->lastname,
                    "city" => $request->city,
                    "flat" => $request->flat,
                    "home" => $request->home,
                    "phone" => $request->phone,
                    "corpus" => $request->corpus,
                    "region" => $request->region,
                    "street" => $request->street,
                    "country" => $country,
                    "postCode" => $request->postCode,
                ];
            }

            $basket = Basket::create([
                'first_name' => $request->firstname,
                'name' => $request->lastname,
                'otchestvo' => $request->otchestvo,
                'email' => $request->email,
                'phone' => $request->phone,
                'fullPrice' => get_cart_items_all_maney() + $deliveryPrice,
                'discount' => get_cart_items_all_discount(),
                'discountUser' => 0,
                'deliveryPrice' => $deliveryPrice,
                'nameDeliveryRu' => $delivery->name_ru,
                'nameDeliveryKz' => $delivery->name_kz,
                'addressDelivery' => $addressDelivery,
                'is_paid' => 'no_oplat',
                'is_complete' => 'no_dostavka',
                'obrabotka' => 'obrabotka',
                'country' => $country,
            ]);


            foreach(get_items_cart() as $key=>$itemCart){
                $shopProduct = ShopProduct::find($itemCart['shopProductId']);
                if($itemCart['variable'] == 'false'){
                    $basket_item = BasketItem::create([
                        'product_name_ru' => $shopProduct->product->name_ru,
                        'product_name_kz' => $shopProduct->product->name_kz,
                        'article' =>$shopProduct->product->article,
                        'nameBrandRu' => $shopProduct->product->brand->name_ru,
                        'nameBrandKz' => $shopProduct->product->brand->name_kz,
                        'price' => $shopProduct->price,
                        'imgUrl'=>$shopProduct->product->gallery[0],
                        'discount' => $shopProduct->discounted_price,
                        'discount_user' => 0,
                        'shopMagazine' =>$shopProduct->shop->toArray(),
                        'quantity' => $itemCart['quantity'],
                        'basket_id' => $basket->id,
                    ]);

                    if($shopProduct->quantity - $itemCart['quantity'] >= 0){
                        $shopProduct->quantity = $shopProduct->quantity - $itemCart['quantity'];
                        $shopProduct->save();
                    }else{
                        $shopProduct->quantity = 0;
                        $shopProduct->save();
                    }
                }else{
                    $variant = $shopProduct->variations;
                    $yourCriteria = $itemCart['variations'];

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
                        'discount_user' => 0,
                        'shopMagazine' =>$shopProduct->shop->toArray(),
                        'quantity' => $itemCart['quantity'],
                        'basket_id' => $basket->id,
                        'isVariation' => true,
                        'nameAttributeRu' => $firstMatch['nameAttribute_ru'],
                        'nameAttributeKz' => $firstMatch['nameAttribute_kz'],
                        'nameVariationRu' => $firstMatch['name_ru'],
                        'nameVariationKz' => $firstMatch['name_kz'],
                    ]);

                    if ($firstMatch['quantity'] - $itemCart['quantity'] >= 0) {
                        $variant = collect($variant)->map(function ($item) use ($firstMatch, $itemCart) {
                            if ($item['name_kz'] == $firstMatch['name_kz'] && 
                                $item['name_ru'] == $firstMatch['name_ru'] && 
                                $item['nameAttribute_kz'] == $firstMatch['nameAttribute_kz'] && 
                                $item['nameAttribute_ru'] == $firstMatch['nameAttribute_ru']) {
                                    $item['quantity'] -= $itemCart['quantity'];
                            }
                            return $item;
                        })->toArray();
                    
                        // Обновление значения в базе данных
                        ShopProduct::find($itemCart['shopProductId'])->update([
                            'variations' => $variant,
                        ]);
                    }else{
                        $variant = collect($variant)->map(function ($item) use ($firstMatch, $itemCart) {
                            if ($item['name_kz'] == $firstMatch['name_kz'] && 
                                $item['name_ru'] == $firstMatch['name_ru'] && 
                                $item['nameAttribute_kz'] == $firstMatch['nameAttribute_kz'] && 
                                $item['nameAttribute_ru'] == $firstMatch['nameAttribute_ru']) {
                                    $item['quantity'] = 0;
                            }
                            return $item;
                        })->toArray();
                    
                        // Обновление значения в базе данных
                        ShopProduct::find($itemCart['shopProductId'])->update([
                            'variations' => $variant,
                        ]);
                    }
                }
            }

            session()->forget('cart');
            $htmlCartItems = view('site.include.cart.cartItems')->render();
            $htmlSuccessView = view('site.cart.success_order_placing',['basket'=>$basket])->render();

            try {
                Mail::send('mails.zakaz', ['basket' => $basket], function($mailMessage) {
                    $mailMessage->to(env('EMAIL_ADMIN'))->subject('Новый заказ');
                });
            } catch (Exception $e) {
                Log::error('почта не работает -- ' . $e->getMessage());
            }

            return response()->json([
                'success' => $basket,
                'countCartItems'=>get_count_cart(),
                'htmlCartItems'=>$htmlCartItems,
                'htmlSuccessView'=>$htmlSuccessView,
            ]);
            
         }
     }
}
