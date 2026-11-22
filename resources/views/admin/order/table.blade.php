{{-- Таблица заказов: единственный partial списка, его используют
     и полная страница, и AJAX-фрагмент живого обновления.
     Водитель и цена правятся прямо в строке (формы с data-атрибутами
     обрабатывает admin-orders-live.js, сохранение - AJAX). --}}
<div class="table-responsive">
    <table class="table table-bordered text-nowrap border-bottom">
        <thead>
            <tr>
                <th class="wd-20p border-bottom-0">Статус заказа</th>
                <th class="wd-15p border-bottom-0">Id</th>
                <th class="wd-15p border-bottom-0">Фамилия клиента</th>
                <th class="wd-15p border-bottom-0">Имя клиента</th>
                <th class="wd-15p border-bottom-0">Телефон клиента</th>
                <th class="wd-20p border-bottom-0">Водитель</th>
                <th class="wd-20p border-bottom-0">Адрес нахождения клиента</th>
                <th class="wd-20p border-bottom-0">Адрес куда клиента</th>
                <th class="wd-15p border-bottom-0">Дистанция</th>
                <th class="wd-15p border-bottom-0">Время в пути</th>
                <th class="wd-15p border-bottom-0">Цена</th>
                <th class="wd-15p border-bottom-0">Класс</th>
                <th class="wd-15p border-bottom-0">Источник</th>
                <th class="wd-15p border-bottom-0">Действие</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($orders as $order)
                <tr data-slug="{{$order->id}}">
                    @if($order->status=="assigned")
                        <td style="background:teal; color:white;">Водитель в пути</td>
                    @elseif($order->status=="completed")
                        <td style="background:green; color:white;">Завершено</td>
                    @elseif($order->status=="canceled")
                        <td style="background:red; color:white;">Отменено</td>
                    @elseif($order->status=="pending")
                        <td style="background:orange; color:white;">Ожидание</td>
                    @elseif($order->status=="searching")
                        <td style="background:blue; color:white;">Ищем водителя</td>
                    @elseif($order->status=="searching_other")
                        <td style="background:#e65100; color:white;">Ищем другого водителя</td>
                    @elseif($order->status=="on_place")
                        <td style="background:purple; color:white;">Водитель на месте</td>
                    @elseif($order->status=="in_progress")
                        <td style="background:navy; color:white;">В дороге</td>
                    @else
                        <td style="background:orange; color:white;">Ожидание</td>
                    @endif

                    <td># {{$order->id}}</td>
                    <td>{{ $order->client_first_name }}</td>
                    <td>{{ $order->client_last_name }}</td>
                    <td>{{ $order->client_phone }}</td>
                    <td>
                        <form data-assign-form data-order-id="{{$order->id}}">
                            <select name="driver_id" class="form-control form-control-sm" data-assign-select style="min-width:170px;">
                                <option value="">Не назначен</option>
                                @foreach($drivers as $driver)
                                    @php($assignState = $driver->assignment_state ?? 'free')
                                    @php($assignBlocked = $assignState !== 'free' && $order->driver_id != $driver->id)
                                    <option value="{{$driver->id}}" @selected($order->driver_id == $driver->id) @disabled($assignBlocked)>{{ $driver->first_name }} {{ $driver->last_name }}@if($assignBlocked) ({{ $assignState === 'busy' ? 'занят' : 'не на линии' }})@endif</option>
                                @endforeach
                            </select>
                        </form>
                    </td>
                    <td>{{ $order->from_address }}</td>
                    <td>{{ $order->to_address }}</td>
                    <td>{{ $order->distance_km !== null ? number_format($order->distance_km, 2, ',', ' ') . ' км' : '—' }}</td>
                    <td>{{ $order->duration_min !== null ? $order->duration_min . ' мин' : '—' }}</td>
                    <td>
                        <form class="d-flex align-items-center" data-price-form data-order-id="{{$order->id}}">
                            <input type="number" name="price" class="form-control form-control-sm" step="0.01" min="0" value="{{ $order->price }}" style="width:90px;">
                            <button type="submit" class="btn btn-sm btn-primary ms-1" title="Сохранить цену"><i class="fa fa-check"></i></button>
                        </form>
                    </td>
                    <td>{{ $order->tariff ? ($order->tariffClass?->label ?? $order->tariff) : '' }}</td>
                    <td>{{ $order->source_label }}</td>
                    <td>
                        @if(in_array($order->status, ['pending', 'searching', 'searching_other']))<a href="{{route('order.edit',['order'=>$order->id])}}" class="btn btn-warning"><i class="fa fa-pencil"></i></a>@endif
                        <button class="btn btn-danger" data-slug="{{$order->id}}" data-name="{{$order->id}}" id="deleteBtn"><i class="fe fe-trash"></i></button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-5">{{$orders->links()}}</div>
