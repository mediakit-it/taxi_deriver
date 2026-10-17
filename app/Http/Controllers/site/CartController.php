<?php

namespace App\Http\Controllers\site;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\ShopProduct;
use App\Models\Cart;
use App\Models\MethodDelivery;

class CartController extends Controller
{
    // Добавление товара в корзину
    public function addToCart(Request $request)
    {
        $user = Auth::guard('web')->user();
        $shopProductId = $request->input('shopProductId');
        $variable = $request->input('variable');
        $variations = $request->input('variations');
        $quantity = $request->input('quantity');

        if($user){
            $country = $user->country;

            // Проверка на существование товара в корзине
            $existingCart = Cart::where([
                ['user_id', $user->id],
                ['shop_product_id', $shopProductId],
                ['variable', $variable],
                ['country', $country]
            ])->first();

            if ($existingCart) {
                if ($variable == 'false') {
                    // Если variable == 'false', обновляем количество
                    $existingCart->quantity = $quantity;
                    $existingCart->save();
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
                    } else {
                        // Если вариации не совпадают, создаем новую запись
                        Cart::create([
                            'user_id' => $user->id,
                            'shop_product_id' => $shopProductId,
                            'variable' => $variable,
                            'variations' => $variations,
                            'quantity' => $quantity,
                            'country' => $country,
                        ]);
                    }
                }
            } else {
                // Если товара нет в корзине, создаем новую запись
                Cart::create([
                    'user_id' => $user->id,
                    'shop_product_id' => $shopProductId,
                    'variable' => $variable,
                    'variations' => $variations,
                    'quantity' => $quantity,
                    'country' => $country,
                ]);
            }

            $htmlCartItems = view('site.include.cart.cartItemsAuth')->render();

            return response()->json([
                'success' => true,
                'message' => 'Товар успешно добавлен в корзину',
                'countCartItems'=>get_count_cart(),
                'htmlCartItems'=>$htmlCartItems,
            ]);

        }else{
            $cart = session()->get('cart', []);
            $found = false;

            // Проверка на существование товара в корзине
            foreach ($cart as $key => $item) {
                if ($item['shopProductId'] == $shopProductId) {
                    if ($variable == 'false') {
                        // Если variable == 'false', обновляем количество
                        $cart[$key]['quantity'] = $quantity;
                        $found = true;
                        break;
                    } else {
                        // Проверяем вариации товара
                        if ($item['variations']['name_ru'] == $variations['name_ru'] &&
                            $item['variations']['name_kz'] == $variations['name_kz'] &&
                            $item['variations']['price'] == $variations['price'] &&
                            $item['variations']['priceDiscount'] == $variations['priceDiscount'] &&
                            $item['variations']['nameAttribute_ru'] == $variations['nameAttribute_ru'] &&
                            $item['variations']['nameAttribute_kz'] == $variations['nameAttribute_kz']) {
                            // Если все вариации совпадают, обновляем количество
                            $cart[$key]['quantity'] = $quantity;
                            $found = true;
                            break;
                        }
                    }
                }
            }

            // Если товар не найден, добавляем новый элемент в корзину
            if (!$found) {
                $cart[] = [
                    'shopProductId' => $shopProductId,
                    'variable' => $variable,
                    'variations' => $variations,
                    'quantity' => $quantity,
                ];
            }

            session()->put('cart', $cart);

            $htmlCartItems = view('site.include.cart.cartItems')->render();

            return response()->json([
                'success' => true,
                'message' => 'Товар успешно добавлен в корзину',
                'countCartItems'=>get_count_cart(),
                'htmlCartItems'=>$htmlCartItems,
            ]);
        }
    }

    // Удаление товара из корзины(переписать под работу 2 корзин)
    public function removeFromCart(Request $request)
    {
        $user = Auth::guard('web')->user();
        $shopProductId = $request->input('shopProductId');

        if($user){
            $cart = Cart::query()->where('id','=', intval($shopProductId))->where('user_id',$user->id)?->delete();
            if($cart){
                $htmlCartItems = view('site.include.cart.cartItemsAuth')->render();
                $htmlIshodCartOrder = view('site.cart.cart_order_update_ishod')->render();
                $methodDeliverys = MethodDelivery::where('country',get_country_from_cookie())->get();
                $htmlIshodPlasing = view('site.cart.plasing_an_order_update_ishod',['methodDeliverys'=>$methodDeliverys])->render();

                return response()->json([
                    'success' => true,
                    'countCartItems'=>get_count_cart(),
                    'htmlCartItems'=>$htmlCartItems,
                    'htmlIshodCartOrder'=>$htmlIshodCartOrder,
                    'htmlIshodPlasing'=>$htmlIshodPlasing,
                ]);
            }else{
                $htmlCartItems = view('site.include.cart.cartItemsAuth')->render();
                $htmlIshodCartOrder = view('site.cart.cart_order_update_ishod')->render();
                $methodDeliverys = MethodDelivery::where('country',get_country_from_cookie())->get();
                $htmlIshodPlasing = view('site.cart.plasing_an_order_update_ishod',['methodDeliverys'=>$methodDeliverys])->render();


                return response()->json([
                    'success' => false,
                    'countCartItems'=>get_count_cart(),
                    'htmlCartItems'=>$htmlCartItems,
                    'htmlIshodCartOrder'=>$htmlIshodCartOrder,
                    'htmlIshodPlasing'=>$htmlIshodPlasing,
                ]);
            }
        }else{
            $cart = session()->get('cart', []);

            if (isset($cart[$shopProductId])) {
                unset($cart[$shopProductId]);
                session()->put('cart', $cart);

                $htmlCartItems = view('site.include.cart.cartItems')->render();
                $htmlIshodCartOrder = view('site.cart.cart_order_update_ishod')->render();
                $methodDeliverys = MethodDelivery::where('country',get_country_from_cookie())->get();
                $htmlIshodPlasing = view('site.cart.plasing_an_order_update_ishod',['methodDeliverys'=>$methodDeliverys])->render();


                return response()->json([
                    'success' => true,
                    'message' => 'Товар успешно добавлен в корзину',
                    'countCartItems'=>get_count_cart(),
                    'htmlCartItems'=>$htmlCartItems,
                    'htmlIshodCartOrder'=>$htmlIshodCartOrder,
                    'htmlIshodPlasing'=>$htmlIshodPlasing,
                ]);
            }

            $htmlCartItems = view('site.include.cart.cartItems')->render();
            $htmlIshodCartOrder = view('site.cart.cart_order_update_ishod')->render();
            $methodDeliverys = MethodDelivery::where('country',get_country_from_cookie())->get();
            $htmlIshodPlasing = view('site.cart.plasing_an_order_update_ishod',['methodDeliverys'=>$methodDeliverys])->render();


            return response()->json([
                'success' => true,
                'message' => 'Товар успешно добавлен в корзину',
                'countCartItems'=>get_count_cart(),
                'htmlCartItems'=>$htmlCartItems,
                'htmlIshodCartOrder'=>$htmlIshodCartOrder,
                'htmlIshodPlasing'=>$htmlIshodPlasing,
            ]);
        }
    }

    // Получение содержимого корзины
    public function getCart()
    {
        // $cart = session()->get('cart', []);
        // return response()->json($cart);

        return view('site.cart.cart_order');
    }

    //оформление заказа
    public function placingAnOrder()
    {
        $methodDeliverys = MethodDelivery::where('country',get_country_from_cookie())->get();
        return view('site.cart.placing_an_order',['methodDeliverys'=>$methodDeliverys]);
    }

    // Обновление количества товара в корзине
    public function updateCart(Request $request)
    {
        $shopProductId = $request->input('shopProductId');
        $quantity = $request->input('quantity');

        $cart = session()->get('cart', []);

        if (isset($cart[$shopProductId])) {
            $cart[$shopProductId]['quantity'] = $quantity;
            session()->put('cart', $cart);

            return response()->json([
                'success' => true,
                'message' => 'Количество товара обновлено',
                'cart' => $cart
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Товар не найден в корзине'
        ]);
    }

    // Увеличение количества товара в корзине
    public function cartPlus(Request $request)
    {
        $user = Auth::guard('web')->user();

        if($user){
            $cart = Cart::find($request->shopProductId);
            $shopProduct = ShopProduct::find($cart->shop_product_id);

            if ($cart->variable == "false") {
                if ($cart->quantity + 1 <= $shopProduct->quantity) {
                    $cart->quantity = $cart->quantity + 1;
                    $cart->save();
                    $found = true;
                }
            }else{
                $variant = $shopProduct->variations;
                $yourCriteria = $cart->variations;

                $filteredVariants = array_filter($variant, function($item) use ($yourCriteria) {
                    return $item['name_kz'] == $yourCriteria['name_kz'] && $item['name_ru'] == $yourCriteria['name_ru'] && $item['nameAttribute_kz'] == $yourCriteria['nameAttribute_kz'] && $item['nameAttribute_ru'] == $yourCriteria['nameAttribute_ru'];
                });

                $firstMatch = reset($filteredVariants);

                if ($firstMatch && $cart->quantity + 1 <= $firstMatch['quantity']) {
                    $cart->quantity = $cart->quantity + 1;
                    $cart->save();
                    $found = true;
                }
            }

            if ($found) {
                $htmlCartItemsTop = view('site.include.cart.cartItemsAuth')->render();
                $htmlCartItems = view('site.include.cart.includeCart.itogCart')->render();
                return response()->json([
                    'success' => true,
                    'moneyOne' => get_price_product_cart($request->shopProductId),
                    'moneyAll' => get_price_product_cart_quantity($request->shopProductId),
                    'moneyNiz' => $htmlCartItems,
                    'htmlCartItems'=>$htmlCartItemsTop,
                    'countCartItems'=>get_count_cart(),
                ]);
            } else {
                return response()->json(['success' => false]);
            }
        }else{
            $carts = session()->get('cart', []);
            $found = false;

            if (isset($carts[$request->shopProductId])) {
                $cart = &$carts[$request->shopProductId];
                $shopProduct = ShopProduct::find($cart['shopProductId']);
                if ($cart['variable'] == "false") {
                    if ($cart['quantity'] + 1 <= $shopProduct->quantity) {
                        $cart['quantity'] = $cart['quantity'] + 1;
                        $found = true;
                    }
                }else{
                    $variant = $shopProduct->variations;
                    $yourCriteria = $cart['variations'];

                    $filteredVariants = array_filter($variant, function($item) use ($yourCriteria) {
                        return $item['name_kz'] == $yourCriteria['name_kz'] && $item['name_ru'] == $yourCriteria['name_ru'] && $item['nameAttribute_kz'] == $yourCriteria['nameAttribute_kz'] && $item['nameAttribute_ru'] == $yourCriteria['nameAttribute_ru'];
                    });

                    $firstMatch = reset($filteredVariants);

                    if ($firstMatch && $cart['quantity'] + 1 <= $firstMatch['quantity']) {
                        $cart['quantity'] = $cart['quantity'] + 1;
                        $found = true;
                    }
                }
            }

            if ($found) {
                session()->put('cart', $carts);
                $htmlCartItemsTop = view('site.include.cart.cartItems')->render();
                $htmlCartItems = view('site.include.cart.includeCart.itogCart')->render();
                return response()->json([
                    'success' => true,
                    'moneyOne' => get_price_product_cart($request->shopProductId),
                    'moneyAll' => get_price_product_cart_quantity($request->shopProductId),
                    'moneyNiz' => $htmlCartItems,
                    'htmlCartItems'=>$htmlCartItemsTop,
                    'countCartItems'=>get_count_cart(),
                ]);
            } else {
                return response()->json(['success' => false]);
            }
        }
    }

    // Уменьшение количества товара в корзине
    public function cartMinus(Request $request)
    {
        $user = Auth::guard('web')->user();

        if($user){
            $cart = Cart::find($request->shopProductId);
            $shopProduct = ShopProduct::find($cart->shop_product_id);

            if ($cart->variable == "false") {
                if ($cart->quantity - 1 >= 1) {
                    $cart->quantity = $cart->quantity - 1;
                    $cart->save();
                    $found = true;
                }
            }else{
                $variant = $shopProduct->variations;
                $yourCriteria = $cart->variations;

                $filteredVariants = array_filter($variant, function($item) use ($yourCriteria) {
                    return $item['name_kz'] == $yourCriteria['name_kz'] && $item['name_ru'] == $yourCriteria['name_ru'] && $item['nameAttribute_kz'] == $yourCriteria['nameAttribute_kz'] && $item['nameAttribute_ru'] == $yourCriteria['nameAttribute_ru'];
                });

                $firstMatch = reset($filteredVariants);

                if ($firstMatch && $cart->quantity - 1 >= 1) {
                    $cart->quantity = $cart->quantity - 1;
                    $cart->save();
                    $found = true;
                }
            }

            if ($found) {
                $htmlCartItemsTop = view('site.include.cart.cartItemsAuth')->render();
                $htmlCartItems = view('site.include.cart.includeCart.itogCart')->render();
                return response()->json([
                    'success' => true,
                    'moneyOne' => get_price_product_cart($request->shopProductId),
                    'moneyAll' => get_price_product_cart_quantity($request->shopProductId),
                    'moneyNiz' => $htmlCartItems,
                    'htmlCartItems'=>$htmlCartItemsTop,
                    'countCartItems'=>get_count_cart(),
                ]);
            } else {
                return response()->json(['success' => false]);
            }
        }else{
            $carts = session()->get('cart', []);
            $found = false;

            if (isset($carts[$request->shopProductId])) {
                $cart = &$carts[$request->shopProductId];
                $shopProduct = ShopProduct::find($cart['shopProductId']);
                if ($cart['variable'] == "false") {
                    if ($cart['quantity'] - 1 >= 1) {
                        $cart['quantity'] = $cart['quantity'] - 1;
                        $found = true;
                    }
                }else{
                    $variant = $shopProduct->variations;
                    $yourCriteria = $cart['variations'];

                    $filteredVariants = array_filter($variant, function($item) use ($yourCriteria) {
                        return $item['name_kz'] == $yourCriteria['name_kz'] && $item['name_ru'] == $yourCriteria['name_ru'] && $item['nameAttribute_kz'] == $yourCriteria['nameAttribute_kz'] && $item['nameAttribute_ru'] == $yourCriteria['nameAttribute_ru'];
                    });

                    $firstMatch = reset($filteredVariants);

                    if ($firstMatch && $cart['quantity'] - 1 >= 1) {
                        $cart['quantity'] = $cart['quantity'] - 1;
                        $found = true;
                    }
                }
            }

            if ($found) {
                session()->put('cart', $carts);
                $htmlCartItemsTop = view('site.include.cart.cartItems')->render();
                $htmlCartItems = view('site.include.cart.includeCart.itogCart')->render();
                return response()->json([
                    'success' => true,
                    'moneyOne' => get_price_product_cart($request->shopProductId),
                    'moneyAll' => get_price_product_cart_quantity($request->shopProductId),
                    'moneyNiz' => $htmlCartItems,
                    'htmlCartItems'=>$htmlCartItemsTop,
                    'countCartItems'=>get_count_cart(),
                ]);
            } else {
                return response()->json(['success' => false]);
            }
        }
    }

    // Получение количества товаров в корзине
    public function getCartCount()
    {
        $cart = session()->get('cart', []);
        $count = array_sum(array_column($cart, 'quantity'));

        return response()->json(['success' => true, 'cartCount' => $count]);
    }

    // Очистка всей корзины
    public function clearCart()
    {
        session()->forget('cart');

        return response()->json([
            'success' => true,
            'message' => 'Корзина очищена',
            'cart' => []
        ]);
    }

    public function deleteOrderCartProduct(Request $request){
        $user = Auth::guard('web')->user();
        $shopProductId = $request->input('shopProductId');
        if($user){
            $cart = Cart::where([['id',$shopProductId],['user_id',$user->id]])->delete();

            if($cart > 0){
                $htmlCartItems = view('site.include.cart.cartItemsAuth')->render();
                $htmlCartItemsNiz = view('site.include.cart.includeCart.itogCart')->render();
                $htmlCartItemsNote = view('site.cart.cart_order_items_note')->render();
                $htmlIshodCartOrder = view('site.cart.cart_order_update_ishod')->render();
                return response()->json([
                    'success' => true,
                    'countCartItems'=>get_count_cart(),
                    'htmlCartItems'=>$htmlCartItems,
                    //$('#cartIshodItems').html(response.htmlIshodCartOrder);
                    'htmlIshodCartOrder'=>$htmlIshodCartOrder,
                    'htmlCartItemsNiz'=>$htmlCartItemsNiz,
                    'htmlCartItemsNote'=>$htmlCartItemsNote,
                ]);
            }else{
                $htmlCartItems = view('site.include.cart.cartItemsAuth')->render();
                $htmlCartItemsNiz = view('site.include.cart.includeCart.itogCart')->render();
                $htmlCartItemsNote = view('site.cart.cart_order_items_note')->render();
                $htmlIshodCartOrder = view('site.cart.cart_order_update_ishod')->render();
                return response()->json([
                    'success' => false,
                    'countCartItems'=>get_count_cart(),
                    'htmlCartItems'=>$htmlCartItems,
                    'htmlIshodCartOrder'=>$htmlIshodCartOrder,
                    'htmlCartItemsNiz'=>$htmlCartItemsNiz,
                    'htmlCartItemsNote'=>$htmlCartItemsNote,
                ]);
            }
        }else{
            $cart = session()->get('cart', []);

            if (isset($cart[$shopProductId])) {
                unset($cart[$shopProductId]);
                session()->put('cart', $cart);

                $htmlCartItems = view('site.include.cart.cartItems')->render();
                $htmlCartItemsNiz = view('site.include.cart.includeCart.itogCart')->render();
                $htmlCartItemsNote = view('site.cart.cart_order_items_note')->render();

                return response()->json([
                    'success' => true,
                    'message' => 'Товар успешно добавлен в корзину',
                    'countCartItems'=>get_count_cart(),
                    'htmlCartItems'=>$htmlCartItems,
                    'htmlCartItemsNiz'=>$htmlCartItemsNiz,
                    'htmlCartItemsNote'=>$htmlCartItemsNote,
                ]);
            }

            $htmlCartItems = view('site.include.cart.cartItems')->render();
            $htmlCartItemsNiz = view('site.include.cart.includeCart.itogCart')->render();
            $htmlCartItemsNote = view('site.cart.cart_order_items_note')->render();

            return response()->json([
                'success' => true,
                'message' => 'Товар успешно добавлен в корзину',
                'countCartItems'=>get_count_cart(),
                'htmlCartItems'=>$htmlCartItems,
                'htmlCartItemsNiz'=>$htmlCartItemsNiz,
                'htmlCartItemsNote'=>$htmlCartItemsNote,
            ]);
        }
    }
}
