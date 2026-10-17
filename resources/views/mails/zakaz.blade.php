<div style="font-family: Arial, sans-serif; font-size: 16px; color: #333;">
    <h2 style="color: #0066cc;">Новый заказ №{{$basket->id}}</h2>

    <h3>Детали заказа:</h3>

    @foreach($basket->basketitems as $item)
    <p><strong>Продукт:</strong> {{ get_languages_from_cookie() == "RUS" ? $item->product_name_ru : $item->product_name_kz }}
    @if($item->isVariation == 1)
        ({{ get_languages_from_cookie() == "RUS" ? $item->nameAttributeRu.': '.$item->nameVariationRu : $item->nameAttributeKz.': '.$item->nameVariationKz }})
    @endif
    </p>
    <p><strong>Количество:</strong> {{$item->quantity}}{{get_languages_from_cookie() == "RUS" ? ' шт.' : ' шт.'}}</p>
    <p><strong>Цена:</strong> {{$item->price}} {{get_valuta_user($basket->country)}}</p>
    @if($item->discount > 0)
    <p style="color: red;"><strong>Скидка:</strong> {{$item->discount}} {{get_valuta_user($basket->country)}}</p>
    @endif
    <p><strong>Оплата:</strong> {{$item->no_payment == 1 ? 'Оплачено' : 'Не оплачено'}}</p>
    <hr>
    @endforeach

    <h3>Итого:</h3>
    <p><strong>Скидка:</strong> {{$basket->discount}} {{get_valuta_user($basket->country)}}</p>
    <p><strong>Скидка пользователя:</strong> {{$basket->discountUser}} {{get_valuta_user($basket->country)}}</p>
    <p><strong>Доставка:</strong> {{$basket->deliveryPrice}} {{get_valuta_user($basket->country)}}</p>
    <p style="font-size: 18px; font-weight: bold; color: #006600;"><strong>Общая сумма:</strong> {{$basket->fullPrice}} {{get_valuta_user($basket->country)}}</p>

    <h3>Статус заказа:</h3>
    <p style="color: #cc0000;">
    @if($basket->obrabotka == "obrabotka")
        На обработке
    @elseif($basket->obrabotka == "obrabotan")
        Обработан
    @elseif($basket->obrabotka == "otmena")
        Отменен
    @else
        Получен
    @endif
    </p>

    @if(get_languages_from_cookie() == "RUS" ? $basket->messageDostavka_ru != null : $basket->messageDostavka_kz != null)
    <h3>Доп. информация:</h3>
    <p>{{get_languages_from_cookie() == "RUS" ? $basket->messageDostavka_ru : $basket->messageDostavka_kz}}</p>
    @endif

    @if($basket->addressDelivery)
    <h3>Адрес доставки:</h3>
    <p><strong>Фамилия:</strong> {{$basket->addressDelivery['first_name']}}</p>
    <p><strong>Имя:</strong> {{$basket->addressDelivery['name']}}</p>
    <p><strong>Страна:</strong> {{$basket->addressDelivery['country']}}</p>
    <p><strong>Область:</strong> {{$basket->addressDelivery['region']}}</p>
    <p><strong>Город:</strong> {{$basket->addressDelivery['city']}}</p>
    <p><strong>Улица:</strong> {{$basket->addressDelivery['street']}}</p>
    <p><strong>Дом:</strong> {{$basket->addressDelivery['home']}}</p>
    <p><strong>Корпус:</strong> {{$basket->addressDelivery['corpus'] ? $basket->addressDelivery['corpus'] : '-'}}</p>
    <p><strong>Квартира:</strong> {{$basket->addressDelivery['flat']}}</p>
    <p><strong>Почтовый код:</strong> {{$basket->addressDelivery['postCode']}}</p>
    <p><strong>Телефон:</strong> {{$basket->addressDelivery['phone'] ? $basket->addressDelivery['phone'] : '-'}}</p> 
    @else
    <h3>Пользователь:</h3>
    <p><strong>Фамилия:</strong> {{$basket->first_name}}</p>
    <p><strong>Имя:</strong> {{$basket->name}}</p>
    <p><strong>Телефон:</strong> {{$basket->phone ? $basket->phone : '-'}}</p> 
    @php
        $user = \App\Models\User::where('email', $basket->email)->first();
    @endphp

    <p><strong>Текущая скидка пользователя:</strong> {{ $user && $user->discountUser ? $user->discountUser : '-' }}</p>

   @endif

</div>