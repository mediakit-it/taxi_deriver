@if(get_count_cart()>0)
    <div class="col-md-6">
        <div class="">
            <h3>Покупатель</h3>
            <div class="col-md-6 item-ip">
                <input type="text" id="firstname" name="firstname" placeholder="Фамилия *" value="{{auth('web')->user() ? auth('web')->user()->first_name : ''}}">
            </div>
            <div class="col-md-6 item-ip">
                <input type="text" id="lastname" name="lastname" placeholder="Имя *" value="{{auth('web')->user() ? auth('web')->user()->name : ''}}">
            </div>
            <div class="col-md-12 item-ip">
                <input type="text" id="otchestvo" name="otchestvo" placeholder="Отчество *" value="{{auth('web')->user() ? auth('web')->user()->otchestvo : ''}}">
            </div>
            <div class="col-md-12 item-ip">
                <input type="email" id="email" name="email" placeholder="E-mail *" value="{{auth('web')->user() ? auth('web')->user()->email : ''}}">
            </div>
            <div class="col-md-12 item-ip">
                <input type="text" id="phone" name="phone" placeholder="Телефон *" value="{{auth('web')->user() ? auth('web')->user()->phone : ''}}">
            </div>
            <div class="col-md-12 item-ip">
                <input type="text" id="country" name="country" value="{{get_country_from_cookie()}}" placeholder="Страна" disabled>
            </div>
            <div class="col-md-12 item-ip">
                <select title="Тип доставки" class="selectpicker" id="methodDelivery">
                    @foreach($methodDeliverys as $methodDelivery)
                        <option value="{{$methodDelivery->id}}">{{get_languages_from_cookie() == "RUS" ? $methodDelivery->name_ru : $methodDelivery->name_kz}}</option>
                    @endforeach
                </select>
            </div>
            
            <div id="addressDelivery" @if($methodDeliverys[0]->isAddressUser=="false") style="display:none;" @endif>
                <div class="col-md-12 item-ip">
                    <input type="text" id="region" name="region" placeholder="Область *">
                </div>
                <div class="col-md-6 item-ip">
                    <input type="text" id="city" name="city" placeholder="Город *">
                </div>
                <div class="col-md-6 item-ip">
                    <input type="text" id="postCode" name="postCode" placeholder="Почтовый индекс *">
                </div>
                <div class="col-md-6 item-ip">
                    <input type="text" id="street" name="street" placeholder="Улица *">
                </div>
                <div class="col-md-6 item-ip">
                    <input type="text" id="home" name="home" placeholder="Дома *">
                </div>
                <div class="col-md-6 item-ip">
                    <input type="text" id="corpus" name="corpus" placeholder="Корпус(если есть)">
                </div>
                <div class="col-md-6 item-ip">
                    <input type="text" id="flat" name="flat" placeholder="Квартира">
                </div>
            </div>
        </div>
        <div class="method">
        </div>
        <div><input class="creat-chk" type="checkbox" id="confirmOrder">Я проверил(а) данные заказа и подтверждаю его правильность.</div>
        <div class="btn-pay">
            <div class="btn-black" style="width: 170px;" id="placeOrderButton">
                <span>Заказать</span>
            </div>
        </div>
    </div>
    <div class="col-md-6 check-right">
        <div class="order">
            <h3>Ваш заказ</h3>
            <table class="cart-table">
                <tbody>
                @auth('web')
                    @foreach(get_items_cart() as $itemCart)
                        <tr>
                            <td>
                                <a href="{{route('site.product',['slug'=>$itemCart->shopProduct->product->slug])}}"><img width="78" height="58" src="{{$itemCart->shopProduct->product->gallery[0]}}" alt=""></a>
                                <!-- <a href="#"><img src="{{asset('assetsSite/images/prd_check_out.png')}}" alt=""></a> -->
                                <div class="prd-infor">
                                    <a href="{{route('site.product',['slug'=>$itemCart->shopProduct->product->slug])}}">{{get_languages_from_cookie() == "RUS" ? $itemCart->shopProduct->product->name_ru : $itemCart->shopProduct->product->name_kz}}</a>
                                    @if($itemCart->variable=='true')
                                        <p>{{get_languages_from_cookie() == "RUS" ? $itemCart->variations['nameAttribute_ru'].': '.$itemCart->variations['name_ru'] : $itemCart->variations['nameAttribute_kz'].': '.$itemCart->variations['name_kz']}}</p>
                                    @endif
                                    <p>Магазин: {{get_languages_from_cookie() == "RUS" ? $itemCart->shopProduct->shop->name_ru.' ('.$itemCart->shopProduct->shop->country.')' : $itemCart->shopProduct->shop->name_kz.' ('.$itemCart->shopProduct->shop->country.')'}}</p>
                                </div>
                            </td>
                            <td><span>x {{$itemCart->quantity}} шт.</span></td>
                            <td>
                                {!! get_price_product_cart($itemCart->id) !!}
                            </td>
                        </tr>
                    @endforeach
                @endauth

                @guest('web')
                    @foreach(get_items_cart() as $key=>$itemCart)
                        @php
                            $shopProduct = \App\Models\ShopProduct::find($itemCart['shopProductId']);
                        @endphp
                        <tr>
                            <td>
                                <a href="{{route('site.product',['slug'=>$shopProduct->product->slug])}}"><img width="78" height="58" src="{{$shopProduct->product->gallery[0]}}" alt=""></a>
                                <!-- <a href="#"><img src="{{asset('assetsSite/images/prd_check_out.png')}}" alt=""></a> -->
                                <div class="prd-infor">
                                    <a href="{{route('site.product',['slug'=>$shopProduct->product->slug])}}">{{get_languages_from_cookie() == "RUS" ? $shopProduct->product->name_ru : $shopProduct->product->name_kz}}</a>
                                    @if($itemCart['variable']=='true')
                                        <p>{{get_languages_from_cookie() == "RUS" ? $itemCart['variations']['nameAttribute_ru'].': '.$itemCart['variations']['name_ru'] : $itemCart['variations']['nameAttribute_kz'].': '.$itemCart['variations']['name_kz']}}</p>
                                    @endif
                                    <p>Магазин: {{get_languages_from_cookie() == "RUS" ? $shopProduct->shop->name_ru.' ('.$shopProduct->shop->country.')' : $shopProduct->shop->name_kz.' ('.$shopProduct->shop->country.')'}}</p>
                                </div>
                            </td>
                            <td><span>x {{$itemCart['quantity']}} шт.</span></td>
                            <td>
                                {!! get_price_product_cart($key) !!}
                            </td>
                        </tr>
                    @endforeach
                @endguest
                    <!-- <tr>
                        <td>
                            <a href="#"><img src="{{asset('assetsSite/images/prd_check_out.png')}}" alt=""></a>
                            <div class="prd-infor">
                                <a href="#">One shoulder bow shop</a>
                                <p>Size: L</p>
                            </div>
                        </td>
                        <td><span>x1</span></td>
                        <td>
                            <p>£250</p>
                        </td>
                    </tr> -->
                </tbody>
            </table>
        </div>
        <div class="comunication">
            <div>
                <b>Сумма: </b>
                <span> {{get_cart_items_all_maney_not_discount()}} {{get_valuta()}}</span>
            </div>
            <div>
                <b>Скидка: </b>
                <span> {{get_cart_items_all_discount()}} {{get_valuta()}}</span>
            </div>
            @auth('web')
                <div>
                    <b>Пользовательская скидка: </b>
                    <span>{{get_cart_items_all_discount_users()}} {{get_valuta()}}</span>
                </div>
            @endauth
            
            <div id="deliveryPricing" @if($methodDeliverys[0]->isMoney=="false") style="display:none;" @endif>
                <b>Доставка: </b>
                @if(get_cart_items_all_maney()>=$methodDeliverys[0]->otPrice)
                    <span>0 {{get_valuta()}}</span>
                @else
                    <span>{{$methodDeliverys[0]->price}} {{get_valuta()}}</span>
                @endif
            </div>

            <div>
                <b>ИТОГО: </b>
                <span id="totalAllItog"> {{get_cart_items_all_maney($methodDeliverys[0])}} {{get_valuta()}}</span>
            </div>
        </div>
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