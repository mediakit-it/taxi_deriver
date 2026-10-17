@if(get_count_cart()>0)
    <form class="cart-form" action="/cart" method="post" novalidate="">
        <div class="table-responsive">
            <table class="table cart-table">
                <thead>
                    <tr>
                        <th style="max-width: 150px;">Товар</th>
                        <th>Магазин</th>
                        <th>Цена</th>
                        <th>Количество</th>
                        <th>Итог</th>
                    </tr>
                </thead>
                <tbody>
                    @auth('web')
                        @foreach(get_items_cart() as $itemCart)
                            <tr data-key="{{$itemCart->id}}">
                                <td class="product-name" style="max-width: 150px;">
                                    <div class="img-prd">
                                        <a href="{{route('site.product',['slug'=>$itemCart->shopProduct->product->slug])}}">
                                            <img src="{{$itemCart->shopProduct->product->gallery[0]}}" alt="">
                                        </a>
                                        <span class="pe-7s-close-circle delete-cart-order-product"></span>
                                    </div>
                                    <div class="prd-infor">
                                        <a href="{{route('site.product',['slug'=>$itemCart->shopProduct->product->slug])}}">{{get_languages_from_cookie() == "RUS" ? $itemCart->shopProduct->product->name_ru : $itemCart->shopProduct->product->name_kz}}</a>
                                        @if($itemCart->variable=='true')
                                            <p>{{get_languages_from_cookie() == "RUS" ? $itemCart->variations['nameAttribute_ru'].': '.$itemCart->variations['name_ru'] : $itemCart->variations['nameAttribute_kz'].': '.$itemCart->variations['name_kz']}}</p>
                                        @endif
                                    </div>
                                </td>
                                <td class="">
                                    {{get_languages_from_cookie() == "RUS" ? $itemCart->shopProduct->shop->name_ru.' ('.$itemCart->shopProduct->shop->country.')' : $itemCart->shopProduct->shop->name_kz.' ('.$itemCart->shopProduct->shop->country.')'}}
                                </td>
                                <td class="product-price">
                                    {!! get_price_product_cart($itemCart->id) !!}
                                </td>
                                <td> 
                                    @if($itemCart->variable == 'true')
                                        @php
                                            $variant = $itemCart->shopProduct->variations;
                                            $yourCriteria = $itemCart->variations;

                                            $filteredVariants = array_filter($variant, function($item) use ($yourCriteria) {
                                                return $item['name_kz'] == $yourCriteria['name_kz'] && $item['name_ru'] == $yourCriteria['name_ru'] && $item['nameAttribute_kz'] == $yourCriteria['nameAttribute_kz'] && $item['nameAttribute_ru'] == $yourCriteria['nameAttribute_ru'];
                                            });

                                            $firstMatch = reset($filteredVariants);
                                        @endphp
                                        <div class="quantity">
                                            <button type="button" class="quantity-right-plus btn btn-number js-plus plus-cs" data-type="plus" data-field="" style="top: 0;">
                                                <span class="plus-icon"><i class="ion-ios-minus-empty"></i></span>
                                            </button>
                                            <input type="text" name="number" value="{{$itemCart->quantity}}" class="product_quantity_number js-number qty" disabled>
                                            <button type="button" class="quantity-left-minus btn btn-number js-minus minus-cs" data-type="minus" data-field="" style="top: 0;"  data-count="{{$firstMatch['quantity']}}">
                                                <span class="minus-icon"><i class="ion-ios-plus-empty"></i></span>
                                            </button>
                                        </div>
                                    @else
                                            <div class="quantity">
                                            <button type="button" class="quantity-right-plus btn btn-number js-plus plus-cs" data-type="plus" data-field="" style="top: 0;">
                                                <span class="plus-icon"><i class="ion-ios-minus-empty"></i></span>
                                            </button>
                                            <input type="text" name="number" value="{{$itemCart->quantity}}" class="product_quantity_number js-number qty" disabled>
                                            <button type="button" class="quantity-left-minus btn btn-number js-minus minus-cs" data-type="minus" data-field="" style="top: 0;"  data-count="{{$itemCart->shopProduct->quantity}}">
                                                <span class="minus-icon"><i class="ion-ios-plus-empty"></i></span>
                                            </button>
                                        </div>
                                    @endif
                                </td>
                                <td class="product-subtotal product-price">
                                    {!! get_price_product_cart_quantity($itemCart->id) !!}
                                    <!-- <p class="price"><span class="money-total">$80.00</span></p> -->
                                </td>
                            </tr>
                        @endforeach
                    @endauth

                    @guest('web')
                        @foreach(get_items_cart() as $key=>$itemCart)
                            @php
                                $shopProduct = \App\Models\ShopProduct::find($itemCart['shopProductId']);
                            @endphp
                            <tr data-key="{{$key}}">
                                <td class="product-name" style="max-width: 150px;">
                                    <div class="img-prd">
                                        <a href="{{route('site.product',['slug'=>$shopProduct->product->slug])}}">
                                        <img src="{{$shopProduct->product->gallery[0]}}" alt="">
                                    </a>
                                    <span class="pe-7s-close-circle delete-cart-order-product"></span>
                                    </div>
                                    <div class="prd-infor">
                                        <a href="{{route('site.product',['slug'=>$shopProduct->product->slug])}}">{{get_languages_from_cookie() == "RUS" ? $shopProduct->product->name_ru : $shopProduct->product->name_kz}}</a>
                                        @if($itemCart['variable']=='true')
                                            <p>{{get_languages_from_cookie() == "RUS" ? $itemCart['variations']['nameAttribute_ru'].': '.$itemCart['variations']['name_ru'] : $itemCart['variations']['nameAttribute_kz'].': '.$itemCart['variations']['name_kz']}}</p>
                                        @endif
                                    </div>
                                </td>
                                <td class="">
                                    {{get_languages_from_cookie() == "RUS" ? $shopProduct->shop->name_ru.' ('.$shopProduct->shop->country.')' : $shopProduct->shop->name_kz.' ('.$shopProduct->shop->country.')'}}
                                </td>
                                <td class="product-price">
                                    {!! get_price_product_cart($key) !!}
                                </td>
                                <td>
                                    @if($itemCart['variable'] == 'true')
                                        @php
                                            $variant = $shopProduct->variations;
                                            $yourCriteria = $itemCart['variations'];

                                            $filteredVariants = array_filter($variant, function($item) use ($yourCriteria) {
                                                return $item['name_kz'] == $yourCriteria['name_kz'] && $item['name_ru'] == $yourCriteria['name_ru'] && $item['nameAttribute_kz'] == $yourCriteria['nameAttribute_kz'] && $item['nameAttribute_ru'] == $yourCriteria['nameAttribute_ru'];
                                            });

                                            $firstMatch = reset($filteredVariants);
                                        @endphp
                                        <div class="quantity">
                                            <button type="button" class="quantity-right-plus btn btn-number js-plus plus-cs" data-type="plus" data-field="" style="top: 0;">
                                                <span class="plus-icon"><i class="ion-ios-minus-empty"></i></span>
                                            </button>
                                            <input type="text" name="number" value="{{$itemCart['quantity']}}" class="product_quantity_number js-number qty" disabled>
                                            <button type="button" class="quantity-left-minus btn btn-number js-minus minus-cs" data-type="minus" data-field="" style="top: 0;"  data-count="{{$firstMatch['quantity']}}">
                                                <span class="minus-icon"><i class="ion-ios-plus-empty"></i></span>
                                            </button>
                                        </div>
                                    @else
                                            <div class="quantity">
                                            <button type="button" class="quantity-right-plus btn btn-number js-plus plus-cs" data-type="plus" data-field="" style="top: 0;">
                                                <span class="plus-icon"><i class="ion-ios-minus-empty"></i></span>
                                            </button>
                                            <input type="text" name="number" value="{{$itemCart['quantity']}}" class="product_quantity_number js-number qty" disabled>
                                            <button type="button" class="quantity-left-minus btn btn-number js-minus minus-cs" data-type="minus" data-field="" style="top: 0;"  data-count="{{$shopProduct->quantity}}">
                                                <span class="minus-icon"><i class="ion-ios-plus-empty"></i></span>
                                            </button>
                                        </div>
                                    @endif
                                </td>
                                <td class="product-subtotal product-price">
                                    {!! get_price_product_cart_quantity($key) !!}
                                </td>
                            </tr>
                        @endforeach
                    @endguest
                </tbody>
            </table>
        </div>
    </form>
    <div class="coupon d-flex justify-content-end">
        <div class="d-flex flex-column">
            <div class="pull-right f20l24 fw400 mb-3">
                Сумма: {{get_cart_items_all_maney_not_discount()}} {{get_valuta()}}
            </div>
            <div class="pull-right f20l24 fw400 mb-3">
                Скидка: {{get_cart_items_all_discount()}} {{get_valuta()}}
            </div>
            @auth('web')
                <div class="pull-right f20l24 fw400 mb-3">
                    Пользовательская скидка: {{get_cart_items_all_discount_users()}} {{get_valuta()}}
                </div>
            @endauth
            <div class="pull-right f20l24 fw400">
                Итого: {{get_cart_items_all_maney()}} {{get_valuta()}}
            </div>
        </div>
    </div>
    <div class="conti d-flex justify-content-between">
        <a href="#" class="btn btn-continue">Продолжить покупки</a>
        <a href="{{route('plasingAnOrder')}}" class="btn btn-buy">Оформление заказа</a>
    </div>

@else 
    <div class="d-flex justify-content-center flex-column">
        <!-- <svg xmlns="http://www.w3.org/2000/svg" class="m-auto mb-4" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="65" height="65" x="0" y="0" viewBox="0 0 682.667 682.667" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><defs><clipPath id="b" clipPathUnits="userSpaceOnUse"><path d="M0 512h512V0H0Z" fill="#424242" opacity="1" data-original="#000000" class=""></path></clipPath></defs><mask id="a"><rect width="100%" height="100%" fill="#ffffff" opacity="1" data-original="#ffffff"></rect></mask><g mask="url(#a)"><g clip-path="url(#b)" transform="matrix(1.33333 0 0 -1.33333 0 682.667)"><path d="M0 0c-3.411-30.354-29.08-53.301-59.625-53.301H-306.75c-30.376 0-55.959 22.7-59.574 52.861l-22.429 187.139" style="stroke-width:40;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1" transform="translate(439.989 73.3)" fill="none" stroke="#424242" stroke-width="40" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity="" data-original="#000000" opacity="1" class=""></path><path d="M0 0h-361v80h472V0H79.966L69.544-92.754" style="stroke-width:40;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1" transform="translate(381 260)" fill="none" stroke="#424242" stroke-width="40" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity="" data-original="#000000" opacity="1" class=""></path><path d="m0 0-108.738 148" style="stroke-width:40;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1" transform="translate(408.738 344)" fill="none" stroke="#424242" stroke-width="40" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity="" data-original="#000000" opacity="1" class=""></path><path d="m0 0-108.738-148" style="stroke-width:40;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1" transform="translate(212.738 492)" fill="none" stroke="#424242" stroke-width="40" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity="" data-original="#000000" opacity="1" class=""></path><path d="M0 0v-80" style="stroke-width:40;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1" transform="translate(256 180)" fill="none" stroke="#424242" stroke-width="40" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity="" data-original="#000000" opacity="1" class=""></path><path d="M0 0v-80" style="stroke-width:40;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1" transform="translate(336 180)" fill="none" stroke="#424242" stroke-width="40" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity="" data-original="#000000" opacity="1" class=""></path><path d="M0 0v-80" style="stroke-width:40;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1" transform="translate(176 180)" fill="none" stroke="#424242" stroke-width="40" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity="" data-original="#000000" opacity="1" class=""></path></g></g></g></svg>     -->
        <svg xmlns="http://www.w3.org/2000/svg" class="m-auto mb-5" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="150" height="150" x="0" y="0" viewBox="0 0 511 511.999" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><linearGradient id="a" x1=".5" x2="512.498" y1="256" y2="256" gradientUnits="userSpaceOnUse"><stop stop-opacity="1" stop-color="#cefe00" offset="0"></stop><stop stop-opacity="1" stop-color="#f3fe03" offset="0.021"></stop><stop stop-opacity="1" stop-color="#fed824" offset="0.293"></stop><stop stop-opacity="1" stop-color="#fedc3c" offset="0.554"></stop><stop stop-opacity="1" stop-color="#fe9d4a" offset="0.796"></stop><stop stop-opacity="1" stop-color="#feb34f" offset="1"></stop></linearGradient><path fill="url(#a)" d="M512.496 172v80c0 11.047-8.953 20-20 20h-13.152l-8.426 74.988c-1.148 10.219-9.805 17.766-19.852 17.77-.746 0-1.496-.043-2.257-.13-10.977-1.23-18.875-11.132-17.641-22.105l10.422-92.753c1.137-10.118 9.691-17.77 19.875-17.77H472.5v-40h-432v40h341c11.047 0 20 8.957 20 20 0 11.047-8.953 20-20 20H74.273l19.75 164.762C96.43 456.852 113.503 472 133.738 472h247.125c20.383 0 37.473-15.277 39.75-35.535 1.23-10.977 11.13-18.871 22.106-17.64 10.976 1.234 18.875 11.132 17.644 22.109-4.554 40.511-38.73 71.066-79.5 71.066H133.738c-40.468 0-74.617-30.3-79.433-70.48L33.988 272H20.5c-11.047 0-20-8.953-20-20v-80c0-11.047 8.953-20 20-20h70.938L197.12 8.16c6.54-8.898 19.055-10.816 27.957-4.273 8.902 6.539 10.817 19.054 4.277 27.957L141.075 152h231.59L284.378 31.844c-6.54-8.903-4.625-21.418 4.277-27.957 8.903-6.543 21.418-4.63 27.961 4.273L422.297 152h70.2c11.046 0 20 8.957 20 20zM236.5 332v80c0 11.047 8.953 20 20 20 11.043 0 20-8.953 20-20v-80c0-11.047-8.957-20-20-20-11.047 0-20 8.953-20 20zm80 0v80c0 11.047 8.953 20 20 20 11.043 0 20-8.953 20-20v-80c0-11.047-8.957-20-20-20-11.047 0-20 8.953-20 20zm-160 0v80c0 11.047 8.953 20 20 20 11.043 0 20-8.953 20-20v-80c0-11.047-8.957-20-20-20-11.047 0-20 8.953-20 20zm0 0" opacity="1" data-original="url(#a)" class=""></path></g></svg>
        <p class="text-center f28l28 fw400 black-clr mb-5">Корзина пуста</p>
        <p class="f16l20 fw-400 mb-5 gray1-clr text-center">Исправить это просто: выберите в каталоге<br> интересующий товар и нажмите кнопку "В корзину"</p>

        <a href="{{route('catalogAll')}}" class="btn btn-continue" style="margin: auto; font-size: 16px;">В каталог</a>
    </div>
@endif