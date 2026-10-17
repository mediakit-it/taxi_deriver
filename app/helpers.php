<?php

use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Auth;

use App\Models\Cart;
use App\Models\ShopProduct;


if (!function_exists('get_languages_from_cookie')) {
    function get_languages_from_cookie()
    {
        $language = Cookie::get('Langviges');

        if ($language == 'russian_lan') {
            return 'RUS';
        } elseif ($language == 'kazach_lan') {
            return 'KAZ';
        } else {
            return 'RUS';
        }
    }
}

if (!function_exists('get_country_from_cookie')) {
    function get_country_from_cookie()
    {
        $user = Auth::guard('web')->user();
        if($user){
            return $user->country;
        }else{
            $country = Cookie::get('Country');

            // Определение результата страны
            if ($country == 'belarus') {
                return 'Беларусь';
            } elseif ($country == 'russia') {
            return 'Россия';
            } elseif ($country == 'kazachstan') {
                return 'Казахстан';
            } else {
                return 'Беларусь';
            }
        }
    }
}

if (!function_exists('get_new_replase_text')) {
    function get_new_replase_text($text, $lineLength = 100)
    {
        $pattern = '/.{1,' . $lineLength . '}(?:\s|$)/u';
        preg_match_all($pattern, $text, $matches);

        $linesArray = $matches[0];

        $firstFiveLines = array_slice($linesArray, 0, 5);

        if (count($linesArray) > 5) {
            $firstFiveLines[] = '...';
        }

        return implode($firstFiveLines);
    }
}

if (!function_exists('get_valuta')) {
    function get_valuta()
    {
        $user = Auth::guard('web')->user();
        if($user){
            if ($user->country == 'Беларусь') {
                return 'BYN';
            } elseif ($user->country == 'Россия') {
                return 'RUB';
            } elseif ($user->country == 'Казахстан') {
                return "KAZ";
            } else {
                return 'BYN';
            }
        }else{
            $country = Cookie::get('Country');

            // Определение результата страны
            if ($country == 'belarus') {
                return 'BYN';
            } elseif ($country == 'russia') {
                return 'RUB';
            } elseif ($country == 'kazachstan') {
                return "KAZ";
            } else {
                return 'BYN';
            }
        }
    }
}

if (!function_exists('get_valuta_user')) {
    function get_valuta_user($country)
    {
        if ($country == 'Беларусь') {
            return 'BYN';
        } elseif ($country == 'Россия') {
            return 'RUB';
        } elseif ($country == 'Казахстан') {
            return "KAZ";
        } else {
            return 'BYN';
        }
    }
}

//Количество товаров в корзине
if (!function_exists('get_count_cart')) {
    function get_count_cart()
    {
        $user = Auth::guard('web')->user();
        if($user){
            $carts = Cart::where([['user_id',$user->id],['country',$user->country]])->get()->sum('quantity');
            return $carts;
        }else{
            $cart = session()->get('cart', []);
            $count = 0;
            foreach ($cart as $item) {
                $count += $item['quantity'];
            }
            return $count;
        }
    }
}

if (!function_exists('is_all_products_for_discounts')){
    function is_all_products_for_discounts()
    {
        $user = Auth::guard('web')->user();
        if($user){
            $carts = Cart::with(['shopProduct'])->where([['user_id',$user->id],['country',$user->country]])->get();
            $allCount = $carts->count();
            $discountCount = $carts->filter(function ($item) {
                if ($item->variable == 'true'){
                    return intval($item->variations['priceDiscount']) > 0;
                }
                else{
                    return intval($item->shopProduct->discounted_price) > 0;
                }
            })->count();
            return $allCount === $discountCount;

        }
        else{
            return false;
        }
    }
}

//Цена всех товаров корзины
if (!function_exists('get_cart_items_all_maney_not_discount')) {
    function get_cart_items_all_maney_not_discount()
    {
        $user = Auth::guard('web')->user();
        if($user){
            $carts = Cart::where([['user_id',$user->id],['country',$user->country]])->get();
            $money = 0;
            foreach($carts as $cart){
                $shop = ShopProduct::find($cart->shop_product_id);
                if($shop->variable=='false'){
                    $money += $shop->price * $cart->quantity;
                }else{
                    $variant = $shop->variations;
                    $yourCriteria = $cart->variations;

                    $filteredVariants = array_filter($variant, function($item) use ($yourCriteria) {
                        return $item['name_kz'] == $yourCriteria['name_kz'] && $item['name_ru'] == $yourCriteria['name_ru'] && $item['nameAttribute_kz'] == $yourCriteria['nameAttribute_kz'] && $item['nameAttribute_ru'] == $yourCriteria['nameAttribute_ru'];
                    });

                    $firstMatch = reset($filteredVariants);

                    $money += $firstMatch['price'] * $cart->quantity;
                }
            }
            return $money;
        }else{
            // return 0;
            $carts = session()->get('cart', []);
            $money = 0;
            foreach($carts as $cart){
                $shop = ShopProduct::find($cart['shopProductId']);
                if($shop->variable=='false'){
                    $money += $shop->price * $cart['quantity'];
                }else{
                    $variant = $shop->variations;
                    $yourCriteria = $cart['variations'];

                    $filteredVariants = array_filter($variant, function($item) use ($yourCriteria) {
                        return $item['name_kz'] == $yourCriteria['name_kz'] && $item['name_ru'] == $yourCriteria['name_ru'] && $item['nameAttribute_kz'] == $yourCriteria['nameAttribute_kz'] && $item['nameAttribute_ru'] == $yourCriteria['nameAttribute_ru'];
                    });

                    $firstMatch = reset($filteredVariants);

                    $money += $firstMatch['price'] * $cart['quantity'];
                }
            }
            return $money;
        }
    }
}

//Цена всех товаров корзины без пользовательских скидок и доставок
if (!function_exists('get_cart_items_all_maney')) {
    function get_cart_items_all_maney($delivery = null)
    {
        $user = Auth::guard('web')->user();
        if($user){
            $carts = Cart::where([['user_id',$user->id],['country',$user->country]])->get();
            $money = 0;
            foreach($carts as $cart){
                $shop = ShopProduct::find($cart->shop_product_id);
                if($shop->variable=='false'){
                    if($shop->discounted_price>0){
                        $money += $shop->discounted_price * $cart->quantity;
                    }else{
                        if($user->discountUser>0 || $user->discountMoney > 0){
                            if($user->country == 'Беларусь'){
                                $money += ($shop->price * $cart->quantity) - (($shop->price * $cart->quantity) * ($user->discountUser/100));
                            }
                            else{
                                $money += ($shop->price * $cart->quantity);
                            }
                        }else{
                            $money += $shop->price * $cart->quantity;
                        }
                    }
                }else{
                    $variant = $shop->variations;
                    $yourCriteria = $cart->variations;

                    $filteredVariants = array_filter($variant, function($item) use ($yourCriteria) {
                        return $item['name_kz'] == $yourCriteria['name_kz'] && $item['name_ru'] == $yourCriteria['name_ru'] && $item['nameAttribute_kz'] == $yourCriteria['nameAttribute_kz'] && $item['nameAttribute_ru'] == $yourCriteria['nameAttribute_ru'];
                    });

                    $firstMatch = reset($filteredVariants);

                    if($firstMatch['priceDiscount']>0){
                        $money += $firstMatch['priceDiscount'] * $cart->quantity;
                    }else{
                        if($user->discountUser>0 || $user->discountMoney > 0){
                            if ($user->country == 'Беларусь'){
                                $money += ($firstMatch['price'] * $cart->quantity) - (($firstMatch['price'] * $cart->quantity) * ($user->discountUser/100));
                            }else{
                                $money += ($firstMatch['price'] * $cart->quantity);
                            }
                        }else{
                            $money += $firstMatch['price'] * $cart->quantity;
                        }
                    }
                }
            }
            if($user->country !== 'Беларусь'){
                if (!is_all_products_for_discounts()){
                    $money -= $user->discountMoney;
                }
            }
            if($delivery!=null && $delivery->isMoney=="true"){
                if($money<$delivery->otPrice){
                    return round($money + $delivery->price);
                }else{
                    return round($money,2);
                }
            }

            return round($money,2);
        }else{
            $carts = session()->get('cart', []);
            $money = 0;
            foreach($carts as $cart){
                $shop = ShopProduct::find($cart['shopProductId']);
                if($shop->variable=='false'){
                    if($shop->discounted_price>0){
                        $money += $shop->discounted_price * $cart['quantity'];
                    }else{
                        $money += $shop->price * $cart['quantity'];
                    }
                }else{
                    $variant = $shop->variations;
                    $yourCriteria = $cart['variations'];

                    $filteredVariants = array_filter($variant, function($item) use ($yourCriteria) {
                        return $item['name_kz'] == $yourCriteria['name_kz'] && $item['name_ru'] == $yourCriteria['name_ru'] && $item['nameAttribute_kz'] == $yourCriteria['nameAttribute_kz'] && $item['nameAttribute_ru'] == $yourCriteria['nameAttribute_ru'];
                    });

                    $firstMatch = reset($filteredVariants);

                    if($firstMatch['priceDiscount']>0){
                        $money += $firstMatch['priceDiscount'] * $cart['quantity'];
                    }else{
                        $money += $firstMatch['price'] * $cart['quantity'];
                    }
                }
            }
            if($delivery!=null && $delivery->isMoney=="true"){
                if($money<$delivery->otPrice){
                    return $money + $delivery->price;
                }else{
                    return $money;
                }
            }
            return $money;
        }
    }
}

//Скидка всех товаров корзины
if (!function_exists('get_cart_items_all_discount')) {
    function get_cart_items_all_discount()
    {
        $user = Auth::guard('web')->user();
        if($user){
            $carts = Cart::where([['user_id',$user->id],['country',$user->country]])->get();
            $money = 0;
            foreach($carts as $cart){
                $shop = ShopProduct::find($cart->shop_product_id);
                if($shop->variable=='false'){
                    if($shop->discounted_price>0){
                        $money += ($shop->price - $shop->discounted_price) * $cart->quantity;
                    }
                }else{
                    $variant = $shop->variations;
                    $yourCriteria = $cart->variations;

                    $filteredVariants = array_filter($variant, function($item) use ($yourCriteria) {
                        return $item['name_kz'] == $yourCriteria['name_kz'] && $item['name_ru'] == $yourCriteria['name_ru'] && $item['nameAttribute_kz'] == $yourCriteria['nameAttribute_kz'] && $item['nameAttribute_ru'] == $yourCriteria['nameAttribute_ru'];
                    });

                    $firstMatch = reset($filteredVariants);

                    if($firstMatch['priceDiscount']>0){
                        $money += ($firstMatch['price'] - $firstMatch['priceDiscount']) * $cart->quantity;
                    }
                }
            }
            return $money;
        }else{
            $carts = session()->get('cart', []);
            $money = 0;
            foreach($carts as $cart){
                $shop = ShopProduct::find($cart['shopProductId']);
                if($shop->variable=='false'){
                    if($shop->discounted_price>0){
                        $money += ($shop->price - $shop->discounted_price) * $cart['quantity'];
                    }
                }else{
                    $variant = $shop->variations;
                    $yourCriteria = $cart['variations'];

                    $filteredVariants = array_filter($variant, function($item) use ($yourCriteria) {
                        return $item['name_kz'] == $yourCriteria['name_kz'] && $item['name_ru'] == $yourCriteria['name_ru'] && $item['nameAttribute_kz'] == $yourCriteria['nameAttribute_kz'] && $item['nameAttribute_ru'] == $yourCriteria['nameAttribute_ru'];
                    });

                    $firstMatch = reset($filteredVariants);

                    if($firstMatch['priceDiscount']>0){
                        $money += ($firstMatch['price'] - $firstMatch['priceDiscount']) * $cart['quantity'];
                    }
                }
            }
            return $money;
        }
    }
}

//Скидка всех товаров корзины пользовательская
if (!function_exists('get_cart_items_all_discount_users')) {
    function get_cart_items_all_discount_users()
    {
        $user = Auth::guard('web')->user();
        if($user){
            $carts = Cart::where([['user_id',$user->id],['country',$user->country]])->get();
            $money = 0;
            $userDiscount = 0;
            if ($user->country != 'Беларусь'){
                if (is_all_products_for_discounts()){
                    return 0;
                }else{
                    return  $user->discountMoney;
                }

            }
            foreach($carts as $cart){
                $shop = ShopProduct::find($cart->shop_product_id);
                if($shop->variable=='false'){
                    if($shop->discounted_price==0){
//                        $user->country = 'Беларусь' ? $user->discountUser : $user->discountMoney;
                        if ($user->country == 'Беларусь'){
                            $money += $shop->price * $cart->quantity * ($user->discountUser/100);
                        }

                    }
                }else{
                    $variant = $shop->variations;
                    $yourCriteria = $cart->variations;

                    $filteredVariants = array_filter($variant, function($item) use ($yourCriteria) {
                        return $item['name_kz'] == $yourCriteria['name_kz'] && $item['name_ru'] == $yourCriteria['name_ru'] && $item['nameAttribute_kz'] == $yourCriteria['nameAttribute_kz'] && $item['nameAttribute_ru'] == $yourCriteria['nameAttribute_ru'];
                    });

                    $firstMatch = reset($filteredVariants);

                    if($firstMatch['priceDiscount']==0){
                        if ($user->country == 'Беларусь'){
                            $money += $firstMatch['price'] * $cart->quantity * ($user->discountUser/100);
                        }

                    }
                }
            }
            return round($money,2);
        }else{

            return 0;
        }
    }
}


//Цена одного продукта в корзине
// if (!function_exists('get_price_product_cart')) {
//     function get_price_product_cart($id)
//     {
//         $user = Auth::guard('web')->user();
//         if($user){
//             $cart = Cart::find($id);
//             $shop = ShopProduct::find($cart->shop_product_id);
//             if($shop->variable=='false'){
//                 if($shop->discounted_price>0){
//                     return '<span class="prosuct-price">'.'<b>'.$shop->discounted_price.' '.get_valuta().'</b>'.'<del>'.$shop->price.' '.get_valuta().'</del>'.'</span>';
//                 }else{
//                     return '<span class="prosuct-price">'.'<b>'.$shop->price.' '.get_valuta().'</b>'.'</span>';
//                 }
//             }else{
//                 $variant = $shop->variations;
//                 $yourCriteria = $cart->variations;


//                 $filteredVariants = array_filter($variant, function($item) use ($yourCriteria) {
//                     return $item['name_kz'] == $yourCriteria['name_kz'] && $item['name_ru'] == $yourCriteria['name_ru'] && $item['nameAttribute_kz'] == $yourCriteria['nameAttribute_kz'] && $item['nameAttribute_ru'] == $yourCriteria['nameAttribute_ru'];
//                 });

//                 $firstMatch = reset($filteredVariants);

//                 if($firstMatch['priceDiscount']>0){
//                     return '<span class="prosuct-price">'.'<b>'.$firstMatch['priceDiscount'].' '.get_valuta().'</b>'.'<del>'.$firstMatch['price'].' '.get_valuta().'</del>'.'</span>';
//                 }else{
//                     return '<span class="prosuct-price">'.'<b>'.$firstMatch['price'].' '.get_valuta().'</b>'.'</span>';
//                 }
//             }
//         }else{
//             $cart = session()->get('cart', []);
//             $shop = ShopProduct::find($cart[$id]['shopProductId']);
//             if($shop->variable=='false'){
//                 if($shop->discounted_price>0){
//                     return '<span class="prosuct-price">'.'<b>'.$shop->discounted_price.' '.get_valuta().'</b>'.'<del>'.$shop->price.' '.get_valuta().'</del>'.'</span>';
//                 }else{
//                     return '<span class="prosuct-price">'.'<b>'.$shop->price.' '.get_valuta().'</b>'.'</span>';
//                 }
//             }else{
//                 $variant = $shop->variations;
//                 $yourCriteria = $cart[$id]['variations'];

//                 $filteredVariants = array_filter($variant, function($item) use ($yourCriteria) {
//                     return $item['name_kz'] == $yourCriteria['name_kz'] && $item['name_ru'] == $yourCriteria['name_ru'] && $item['nameAttribute_kz'] == $yourCriteria['nameAttribute_kz'] && $item['nameAttribute_ru'] == $yourCriteria['nameAttribute_ru'];
//                 });

//                 $firstMatch = reset($filteredVariants);

//                 if($firstMatch['priceDiscount']>0){
//                     return '<span class="prosuct-price">'.'<b>'.$firstMatch['priceDiscount'].' '.get_valuta().'</b>'.'<del>'.$firstMatch['price'].' '.get_valuta().'</del>'.'</span>';
//                 }else{
//                     return '<span class="prosuct-price">'.'<b>'.$firstMatch['price'].' '.get_valuta().'</b>'.'</span>';
//                 }
//             }

//         }
//     }
// }

//Цена одного продукта в корзине (изменено)
if (!function_exists('get_price_product_cart')) {
    function get_price_product_cart($id)
    {
        $user = Auth::guard('web')->user();
        if($user){
            $cart = Cart::find($id);
            $shop = ShopProduct::find($cart->shop_product_id);
            if($shop->variable=='false'){
                if($shop->discounted_price>0){
                    return '<div class="prosuct-price">'.'<p class="mb-1"><del class="f12l16">'.$shop->price.' '.get_valuta().'</del></p>'.'<p class="mb-1 black-clr"><b>'.$shop->discounted_price.' '.get_valuta().'</b>'.'</p>'.'</div>';
                }else{
                    //скидка пользователя
                    if($user->discountUser>0 || $user->discountMoney > 0){
                        if ($user->country == 'Беларусь'){
                            return '<div class="prosuct-price">'.'<p class="mb-1"><del class="f12l16">'.$shop->price.' '.get_valuta().'</del></p>'.'<p class="mb-1 black-clr"><b>'.round($shop->price-($shop->price*($user->discountUser/100)),2).' '.get_valuta().'</b>'.'</p>'.'</div>';
                        }
                        else{
                            return '<div class="prosuct-price">'.'<p class="mb-1">'.$shop->price.' '.get_valuta().'</p></div>';
                        }
                    }else{
                        return '<span class="prosuct-price">'.'<b>'.$shop->price.' '.get_valuta().'</b>'.'</span>';
                    }
                }
            }else{
                $variant = $shop->variations;
                $yourCriteria = $cart->variations;


                $filteredVariants = array_filter($variant, function($item) use ($yourCriteria) {
                    return $item['name_kz'] == $yourCriteria['name_kz'] && $item['name_ru'] == $yourCriteria['name_ru'] && $item['nameAttribute_kz'] == $yourCriteria['nameAttribute_kz'] && $item['nameAttribute_ru'] == $yourCriteria['nameAttribute_ru'];
                });

                $firstMatch = reset($filteredVariants);

                if($firstMatch['priceDiscount']>0){
                    return '<div class="prosuct-price">'.'<p class="mb-1"><del class="f12l16">'.$firstMatch['price'].' '.get_valuta().'</del></p>'.'<p class="mb-1 black-clr"><b>'.$firstMatch['priceDiscount'].' '.get_valuta().'</b>'.'</p>'.'</div>';
                }else{
                    //скидка пользователя
                    if($user->discountUser>0 || $user->discountMoney > 0){
                        if ($user->country == 'Беларусь'){
                            return '<div class="prosuct-price">'.'<p class="mb-1"><del class="f12l16">'.$firstMatch['price'].' '.get_valuta().'</del></p>'.'<p class="mb-1 black-clr"><b>'.round($firstMatch['price']-($firstMatch['price']*($user->discountUser/100)),2).' '.get_valuta().'</b>'.'</p>'.'</div>';
                        }else{
                            return '<div class="prosuct-price">'.'<p class="mb-1">'.$firstMatch['price'].' '.get_valuta().'</p>'.'</div>';
                        }

                    }else{
                        return '<span class="prosuct-price">'.'<b>'.$firstMatch['price'].' '.get_valuta().'</b>'.'</span>';
                    }
                }
            }
        }else{
            $cart = session()->get('cart', []);
            $shop = ShopProduct::find($cart[$id]['shopProductId']);
            if($shop->variable=='false'){
                if($shop->discounted_price>0){
                    return '<div class="prosuct-price">'.'<p class="mb-1"><del class="f12l16">'.$shop->price.' '.get_valuta().'</del></p>'.'<p class="mb-1 black-clr"><b>'.$shop->discounted_price.' '.get_valuta().'</b>'.'</p>'.'</div>';
                }else{
                    return '<span class="prosuct-price">'.'<b>'.$shop->price.' '.get_valuta().'</b>'.'</span>';
                }
            }else{
                $variant = $shop->variations;
                $yourCriteria = $cart[$id]['variations'];

                $filteredVariants = array_filter($variant, function($item) use ($yourCriteria) {
                    return $item['name_kz'] == $yourCriteria['name_kz'] && $item['name_ru'] == $yourCriteria['name_ru'] && $item['nameAttribute_kz'] == $yourCriteria['nameAttribute_kz'] && $item['nameAttribute_ru'] == $yourCriteria['nameAttribute_ru'];
                });

                $firstMatch = reset($filteredVariants);

                if($firstMatch['priceDiscount']>0){
                    return '<div class="prosuct-price">'.'<p class="mb-1"><del class="f12l16">'.$firstMatch['price'].' '.get_valuta().'</del></p>'.'<p class="mb-1 black-clr"><b>'.$firstMatch['priceDiscount'].' '.get_valuta().'</b>'.'</p>'.'</div>';
                }else{
                    return '<span class="prosuct-price">'.'<b>'.$firstMatch['price'].' '.get_valuta().'</b>'.'</span>';
                }
            }

        }
    }
}

//Цена одного продукта в корзине c количеством
if (!function_exists('get_price_product_cart_quantity')) {
    function get_price_product_cart_quantity($id)
    {
        $user = Auth::guard('web')->user();
        if($user){
            $cart = Cart::find($id);
            $shop = ShopProduct::find($cart->shop_product_id);
            if($shop->variable=='false'){
                if($shop->discounted_price>0){
                    return '<span class="prosuct-price">'.'<b>'.($shop->discounted_price * $cart->quantity).' '.get_valuta().'</b>'.'</span>';
                }else{
                    if($user->discountUser>0 || $user->discountMoney > 0 ){
                        if ($user->country == 'Беларусь'){
                            return '<span class="prosuct-price">'.'<b>'.round((($shop->price * $cart->quantity) - (($shop->price * $cart->quantity) * ($user->discountUser/100))),2).' '.get_valuta().'</b>'.'</span>';
                        }
                        else{
                            return '<span class="prosuct-price">'.'<b>'.round((($shop->price * $cart->quantity)),2).' '.get_valuta().'</b>'.'</span>';
                        }
                    }else{
                        return '<span class="prosuct-price">'.'<b>'.($shop->price * $cart->quantity).' '.get_valuta().'</b>'.'</span>';
                    }
                }
            }else{
                $variant = $shop->variations;
                $yourCriteria = $cart->variations;


                $filteredVariants = array_filter($variant, function($item) use ($yourCriteria) {
                    return $item['name_kz'] == $yourCriteria['name_kz'] && $item['name_ru'] == $yourCriteria['name_ru'] && $item['nameAttribute_kz'] == $yourCriteria['nameAttribute_kz'] && $item['nameAttribute_ru'] == $yourCriteria['nameAttribute_ru'];
                });

                $firstMatch = reset($filteredVariants);

                if($firstMatch['priceDiscount']>0){
                    return '<span class="prosuct-price">'.'<b>'.($firstMatch['priceDiscount'] * $cart->quantity).' '.get_valuta().'</b>'.'</span>';
                }else{
                    if($user->discountUser>0 || $user->discountMoney > 0 ){
                        if ($user->country == 'Беларусь'){
                            return '<span class="prosuct-price">'.'<b>'.round((($firstMatch['price'] * $cart->quantity) - (($firstMatch['price'] * $cart->quantity) * ($user->discountUser/100))),2).' '.get_valuta().'</b>'.'</span>';
                        }
                        else{
                            return '<span class="prosuct-price">'.'<b>'.round((($firstMatch['price'] * $cart->quantity)),2).' '.get_valuta().'</b>'.'</span>';
                        }

                    }else{
                        return '<span class="prosuct-price">'.'<b>'.($firstMatch['price'] * $cart->quantity).' '.get_valuta().'</b>'.'</span>';
                    }
                }
            }
        }else{
            $cart = session()->get('cart', []);
            $shop = ShopProduct::find($cart[$id]['shopProductId']);
            if($shop->variable=='false'){
                if($shop->discounted_price>0){
                    return '<span class="prosuct-price">'.'<b>'.($shop->discounted_price * $cart[$id]['quantity']).' '.get_valuta().'</b>'.'</span>';
                }else{
                    return '<span class="prosuct-price">'.'<b>'.($shop->price * $cart[$id]['quantity']).' '.get_valuta().'</b>'.'</span>';
                }
            }else{
                $variant = $shop->variations;
                $yourCriteria = $cart[$id]['variations'];

                $filteredVariants = array_filter($variant, function($item) use ($yourCriteria) {
                    return $item['name_kz'] == $yourCriteria['name_kz'] && $item['name_ru'] == $yourCriteria['name_ru'] && $item['nameAttribute_kz'] == $yourCriteria['nameAttribute_kz'] && $item['nameAttribute_ru'] == $yourCriteria['nameAttribute_ru'];
                });

                $firstMatch = reset($filteredVariants);

                if($firstMatch['priceDiscount']>0){
                    return '<span class="prosuct-price">'.'<b>'.($firstMatch['priceDiscount'] * $cart[$id]['quantity']).' '.get_valuta().'</b>'.'</span>';
                }else{
                    return '<span class="prosuct-price">'.'<b>'.($firstMatch['price'] * $cart[$id]['quantity']).' '.get_valuta().'</b>'.'</span>';
                }
            }

        }
    }
}

//Получение товаров из корзины
if (!function_exists('get_items_cart')) {
    function get_items_cart()
    {
        $user = Auth::guard('web')->user();
        if($user){
            $carts = Cart::where([['user_id',$user->id],['country',$user->country]])->get();
            return $carts;
        }else{

            return session()->get('cart', []);
        }
    }
}

if (!function_exists('get_price_range')) {
    function get_price_range($shopProducts): string
    {
        if (empty($shopProducts)) {
            return 'N/A';
        } else {
            $minPrice = PHP_FLOAT_MAX;
            $maxPrice = 0;
            foreach ($shopProducts->shopProductsWeb as $product) {
                if ($product['variable'] === 'false') {
                    $price = (float)$product['price'];
                    if ($price < $minPrice) {
                        $minPrice = $price;
                    }
                    if ($price > $maxPrice) {
                        $maxPrice = $price;
                    }
                } else {
                    if (count($product['variations']) == 0){
                        $price = (float)$product['price'];
                        if ($price < $minPrice) {
                            $minPrice = $price;
                        }
                        if ($price > $maxPrice) {
                            $maxPrice = $price;
                        }
                    }else{
                        foreach ($product['variations'] as $variation) {
                            if ($variation['quantity'] > 0){
                                $price = (float)$variation['price'];
                                if ($price < $minPrice) {
                                    $minPrice = $price;
                                }
                                if ($price > $maxPrice) {
                                    $maxPrice = $price;
                                }
                            }

                        }
                    }
                }
            }

            if ($minPrice == $maxPrice) {
                return number_format($minPrice, 2).' '.get_valuta();
            } else {
                return number_format($minPrice, 2) . ' - ' . number_format($maxPrice, 2).' '.get_valuta();
            }
        }
    }
}
