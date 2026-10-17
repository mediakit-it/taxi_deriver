<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Product;
use App\Models\ShopProduct;
use App\Models\Category;
use App\Models\MethodDelivery;
use App\Models\Cart;
use Illuminate\Support\Collection;

use Illuminate\Support\Facades\Log;

class CartController extends Controller
{

    public function addCartUser(Request $request){
        $user = Auth::user();
        if ($user) {
            $country = $user->country;
            $variations = $request->input('variations');
            $quantity = $request->input('quantity');

            // Проверка на существование товара в корзине
            $existingCart = Cart::where([
                ['user_id', $user->id],
                ['shop_product_id', $request->input('shopProductId')],
                ['variable', $request->input('variable')],
                ['country', $country]
            ])->first();

            if ($existingCart) {
                if ($request->input('variable') == 'false') {
                    // Если variable == 'false', обновляем количество
                    $existingCart->quantity = $quantity;
                    $existingCart->save();

                    return response()->json([
                        'success' => 'true',
                        'message' => 'Товар успешно добавлен в корзину',
                    ]);

                } else {
                    // Проверяем вариации товара
                    $existingVariations = $existingCart->variations;

                    if ($existingVariations['name_ru'] == $variations['name_ru'] &&
                        $existingVariations['name_kz'] == $variations['name_kz'] &&
                        $existingVariations['price'] == $variations['price'] &&
                        $existingVariations['priceDiscount'] == $variations['priceDiscount'] &&
                        $existingVariations['nameAttribute_ru'] == $variations['nameAttribute_ru'] &&
                        $existingVariations['nameAttribute_kz'] == $variations['nameAttribute_kz']) {
                        // Если все вариации совпадают, обновляем количество
                        $existingCart->quantity = $quantity;
                        $existingCart->save();

                        return response()->json([
                            'success' => 'true',
                            'message' => 'Товар успешно добавлен в корзину',
                        ]);

                    } else {
                        // Если вариации не совпадают, создаем новую запись
                        $newCart = Cart::create([
                            'user_id' => $user->id,
                            'shop_product_id' => $request->input('shopProductId'),
                            'variable' => $request->input('variable'),
                            'variations' => $request->input('variations'),
                            'quantity' => $request->input('quantity'),
                            'country' => $country,
                        ]);

                        return response()->json([
                            'success' => 'true',
                            'message' => 'Товар успешно добавлен в корзину',
                        ]);
                    }
                }
            } else {
                // Если товара нет в корзине, создаем новую запись
                $newCart = Cart::create([
                    'user_id' => $user->id,
                    'shop_product_id' => $request->input('shopProductId'),
                    'variable' => $request->input('variable'),
                    'variations' => $request->input('variations'),
                    'quantity' => $request->input('quantity'),
                    'country' => $country,
                ]);

                return response()->json([
                    'success' => 'true',
                    'message' => 'Товар успешно добавлен в корзину',
                ]);
            }
           
            // $newCart = Cart::create([
            //     'user_id' => $user->id,
            //     'shop_product_id' => $request->input('shopProductId'),
            //     'variable' => $request->input('variable'),
            //     'variations' => $request->input('variations'),
            //     'quantity' => $request->input('quantity'),
            //     'country' => $country,
            // ]);
            return response()->json([
                'success' => 'false',
                'message' => 'Товар не был добавлен в корзину',
            ]);
            // if($newCart){
            //     return response()->json([
            //         'success' => 'true',
            //         'message' => 'Товар успешно добавлен в корзину',
            //     ]);
            // }else{
                // return response()->json([
                //     'success' => 'false',
                //     'message' => 'Товар не был добавлен в корзину',
                // ]);
            // }
        } else {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
    }

    public function getCartsUser(){
        $user = Auth::user();
        if ($user) {
            $country = $user->country;

            $carts = Cart::where([['user_id', $user->id],['country', $country]])->with('shopProduct.product.brand','shopProduct.shop')->get();

            $deliverys = MethodDelivery::where('country', $country)->get();

            return response()->json([
                'success' => 'true',
                'carts' => $carts,
                'deliverys' => $deliverys,
            ]);

        }else{
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
    }

    public function getCountCartUser(){
        $user = Auth::user();
        if ($user) {
            $country = $user->country;

            $carts = Cart::where([['user_id', $user->id],['country', $country]])->with('shopProduct.product')->count();

            return response()->json([
                'success' => 'true',
                'cartsCount' => $carts,
            ]);

        }else{
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
    }

    public function cartUserPlus(Request $request){
        $user = Auth::user();
        if ($user) {
            $country = $user->country;

            $carts = Cart::find($request->input('cart_id'));

            if($carts->variable == 'false'){
                if($carts->quantity + 1 <= ShopProduct::find($carts->shop_product_id)->quantity){
                    $carts->quantity +=1;
                    $carts->save();
                    return response()->json([
                        'success' => 'true',
                    ]);
                }else{
                    return response()->json([
                        'success' => 'false',
                        'message' => 'У вас максимальное количество товара'
                    ]);
                }
            }else{
                $variant = ShopProduct::find($carts->shop_product_id)->variations;
                $yourCriteria = $carts->variations;

                // Предположим, что $variant - это массив вариаций
                $filteredVariants = array_filter($variant, function($item) use ($yourCriteria) {
                    // Здесь $yourCriteria - это ваш критерий, который определяет, какой элемент вы хотите получить
                    return $item['name_kz'] == $yourCriteria['name_kz'] && $item['name_ru'] == $yourCriteria['name_ru'] && $item['nameAttribute_kz'] == $yourCriteria['nameAttribute_kz'] && $item['nameAttribute_ru'] == $yourCriteria['nameAttribute_ru'];
                });

                // Если вы хотите получить только первый элемент, который соответствует вашим критериям
                $firstMatch = reset($filteredVariants);

                if($carts->quantity + 1 <= $firstMatch['quantity']){

                    $carts->quantity +=1;
                    $carts->save();
                    return response()->json([
                        'success' => 'true',
                    ]);
                }else{
                    return response()->json([
                        'success' => 'false',
                        'message' => 'У вас выбранно максимальное количество товара'
                    ]);
                }


                // return $firstMatch;
                return response()->json([
                    'success' => 'false',
                    'message' => 'У вас максимальное количество товара'
                ]);
            }

            

        }else{
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
    }

    public function cartUserMinus(Request $request){
        $user = Auth::user();
        if ($user) {
            $country = $user->country;

            $carts = Cart::find($request->input('cart_id'));

            if($carts->quantity - 1 > 0){
                $carts->quantity -=1;
                $carts->save();
                return response()->json([
                    'success' => 'true',
                ]);
            }else{
                $carts->delete();
                return response()->json([
                    'success' => 'false',
                ]);
            }

        }else{
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
    }
}
