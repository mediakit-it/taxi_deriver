<div class="f28l28 text-center mb-5">Ваш заказ №{{$basket->id}} успешно создан</div>
<div class="orders-wrapper flex-wrap flex-md-nowrap">
    <div class="orders-left">
        @foreach($basket->basketitems as $item)
            <div class="order_product__details d-flex gap-5">
                <div class="d-flex gap-5">
                    <div class="product_image">
                    @if($item->imgUrl)
                        <img src="{{$item->imgUrl}}" alt="product-img">
                    @else
                        <img src="{{asset('assetsSite/images/no_image.jpg')}}" alt="product-img">
                    @endif
                    </div>
                    <div>
                        <div class="product_name">
                        <p class="black-clr">
                            {{ get_languages_from_cookie() == "RUS" ? $item->product_name_ru : $item->product_name_kz }}
                            {{$item->isVariation == 1 ? (
                                get_languages_from_cookie() == "RUS" ? 
                                '('.$item->nameAttributeRu.': '.$item->nameVariationRu.')' : 
                                '('.$item->nameAttributeKz.': '.$item->nameVariationKz.')'
                            ) : ''}}
                        </p>
                        </div>
                        <div class="f12l16">Можно забрать в магазине: {{$item->shopMagazine['name_ru']}}({{$item->shopMagazine['country']}})</div>
                    </div>
                </div>
                <div class="product_cost ms-auto">
                    <div class="product_price f20l24 d-flex flex-column">
                        @if($item->discount>0)
                        <span class="f12l16 text-right"><del>{{$item->price}} {{get_valuta_user($basket->country)}}</del></span>
                        <span>{{$item->discount}} {{get_valuta_user($basket->country)}}</span>
                        @else
                        <span>{{$item->price}} {{get_valuta_user($basket->country)}}</span>
                        @endif
                    </div>
                    <div class="product_quantity f12l16 text-right">
                        <span>х {{$item->quantity}}{{get_languages_from_cookie() == "RUS" ? 'шт.':'шт.'}}</span>
                    </div>
                    <div class="text-right">
                    @if ($item->no_payment == 1)
                        <span class="f12l16" style="color:green !important;">Оплачено</span>
                    @else
                        <span class="f12l16" style="color:red !important;">Не оплачено</span>
                    @endif
                    </div>
                </div>
            </div>
        @endforeach
        <div class="total_block">
            <div class="total_price_div d-flex justify-content-between">
                <span class="f16l20">Скидка:</span>
                <span class="f18l22">{{$basket->discount}} {{get_valuta_user($basket->country)}}</span>
            </div>
            <div class="total_price_div d-flex justify-content-between">
                <span class="f16l20">Скидка пользователя:</span>
                <span class="f18l22">{{$basket->discountUser}} {{get_valuta_user($basket->country)}}</span>
            </div>
            <div class="total_price_div d-flex justify-content-between">
                <span class="f16l20">Доставка:</span>
                <span class="f18l22">{{$basket->deliveryPrice}} {{get_valuta_user($basket->country)}}</span>
            </div>
            <div class="total_price_div d-flex justify-content-between">
                <span class="f16l20 fw600">Итого:</span>
                <span class="f18l22">{{$basket->fullPrice}} {{get_valuta_user($basket->country)}}</span>
            </div>
        </div>
    </div>
    <div class="orders-right d-flex flex-column flex-sm-row flex-md-column">
        <div class="rightside_block order_status_block" data-id="$basket->id" data-url-update="">
            <div class="f20l24 fw60">
            Статус заказа:
            @if($basket->obrabotka == "obrabotka")
                <span class="f14l20 mt-4"> На обработке </sp>
            @elseif($basket->obrabotka == "obrabotan")
                <span class="f14l20 mt-4"> Обработан </sp>
            @elseif($basket->obrabotka == "otmena")
                <span class="f14l20 mt-4"> Отменен </sp>
            @else
                <span class="f14l20 mt-4"> Получен </sp>
            @endif
            </div>

            @if(get_languages_from_cookie() == "RUS" ? $basket->messageDostavka_ru != null : $basket->messageDostavka_kz != null)
            <div class="f20l24 fw60">
                Доп. информация:  <span class="f14l20 mt-4"> {{get_languages_from_cookie() == "RUS" ? $basket->messageDostavka_ru : $basket->messageDostavka_kz}} </sp>
            </div>
            @endif
            
        </div>
        <div class="rightside_block delivery_block">
            <div class="f20l24 fw600">
            Доставка:
            <span class="f14l20 mt-4">{{get_languages_from_cookie() == "RUS" ? $basket->nameDeliveryRu : $basket->nameDeliveryKz}}</span>
            </div>
            @if($basket->addressDelivery)
            <div class="f20l24 fw600 mt-4 mb-2">Адрес доставки:</div>

            <dt class="col-sm-6 px-0">Фамилия: </dt>
            <dd class="col-sm-6 px-0">{{$basket->addressDelivery['first_name']}}</dd>

            <dt class="col-sm-6 px-0">Имя: </dt>
            <dd class="col-sm-6 px-0">{{$basket->addressDelivery['name']}}</dd>

            <dt class="col-sm-6 px-0">Страна: </dt>
            <dd class="col-sm-6 px-0">{{$basket->addressDelivery['country']}}</dd>

            <dt class="col-sm-6 px-0">Область: </dt>
            <dd class="col-sm-6 px-0">{{$basket->addressDelivery['region']}}</dd>

            <dt class="col-sm-6 px-0">Город: </dt>
            <dd class="col-sm-6 px-0">{{$basket->addressDelivery['city']}}</dd>

            <dt class="col-sm-6 px-0">Улица: </dt>
            <dd class="col-sm-6 px-0">{{$basket->addressDelivery['street']}}</dd>

            <dt class="col-sm-6 px-0">Дом: </dt>
            <dd class="col-sm-6 px-0">{{$basket->addressDelivery['home']}}</dd>

            <dt class="col-sm-6 px-0">Корпус: </dt>
            <dd class="col-sm-6 px-0">{{$basket->addressDelivery['corpus'] ? $basket->addressDelivery['corpus'] : '-'}}</dd>

            <dt class="col-sm-6 px-0">Квартира: </dt>
            <dd class="col-sm-6 px-0">{{$basket->addressDelivery['flat']}}</dd>

            <dt class="col-sm-6 px-0">Почтовый код: </dt>
            <dd class="col-sm-6 px-0">{{$basket->addressDelivery['postCode']}}</dd>
        @endif
            
        </div>
        <!-- <div class="rightside_block payment_block">
            <div class="f20l24 fw60">Оплата:</div>
            <p class="f14l20 mt-4">$basket->payment_type</p>
        </div> -->
    </div>
</div>