<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Car;
use App\Models\Order;
use App\Models\User;
use App\Models\DriverLocation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Events\OrderSentClose;
use App\Events\UserOrderSent;
use App\Events\OrderSentNewDriving;
use App\Events\ClientOrderUpdated;
use App\Events\UserOrderUpdate;

use Illuminate\Support\Facades\Http;

use App\Events\OrderCreated;
use App\Events\OrderStatusChanged; // Добавлено RAY
use App\Events\AdminAlert;

class OrderController extends Controller
{
    private function logOrderActivity($action, $data = [], $user = null)
    {
        $logData = [
            'action' => $action,
            'timestamp' => now()->toDateTimeString(),
            'user_id' => $user ? $user->id : null,
            'user_role' => $user ? $user->role : null,
            'user_email' => $user ? $user->email : null,
            'data' => $data
        ];
        
        Log::channel('orders')->info(json_encode($logData, JSON_UNESCAPED_UNICODE));
    }

    public function index()
    {
        $user = Auth::user();

        $this->logOrderActivity('INDEX_ORDERS', [
            'user_id' => $user ? $user->id : null,
            'user_role' => $user ? $user->role : null
        ], $user);

        if (!$user) {
            $this->logOrderActivity('INDEX_ORDERS_FAILED', ['reason' => 'Unauthenticated']);
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        if($user->role=='driving') {
            $orders = Order::where('driver_id',$user->id)->whereNotNull('driver_id')->orderBy('id','desc')->get();
            $this->logOrderActivity('INDEX_ORDERS_DRIVER', [
                'driver_id' => $user->id,
                'orders_count' => $orders->count()
            ], $user);
        } else {
            $orders = Order::where('client_id',$user->id)->orderBy('id','desc')->with('driver')->get();
            $this->attachClientTripData($orders);
            $this->logOrderActivity('INDEX_ORDERS_CLIENT', [
                'client_id' => $user->id,
                'orders_count' => $orders->count()
            ], $user);
        }

        return response()->json(['data' => $orders]);
    }


    // Клиентское представление поездки в списке заказов клиента.
    // Машина, которую парсит приложение (driver.car), должна быть
    // машиной ЭТОЙ поездки: сначала orders.car_id, затем текущая
    // привязка водителя (users.current_car_id - у каждого
    // водительского устройства авто привязано) и только в конце
    // legacy-связь User::car(). Плюс последняя известная позиция
    // водителя (driver_location) для активных поездок: приложение
    // ставит по ней метку машины до первого живого события.
    private function attachClientTripData($orders): void
    {
        $activeStatuses = [Order::STATUS_ASSIGNED, Order::STATUS_ON_PLACE, Order::STATUS_IN_PROGRESS];

        $carIds = $orders->flatMap(function (Order $order) {
            return [$order->car_id, $order->driver?->current_car_id];
        })->filter()->unique();

        $cars = $carIds->isEmpty()
            ? collect()
            : Car::whereIn('id', $carIds)->get()->keyBy('id');

        $driverIds = $orders
            ->filter(fn (Order $order) => $order->driver_id && in_array($order->status, $activeStatuses, true))
            ->pluck('driver_id')
            ->unique();

        $locations = $driverIds->isEmpty()
            ? collect()
            : DriverLocation::whereIn('user_id', $driverIds)->get()->keyBy('user_id');

        foreach ($orders as $order) {
            $driver = $order->driver;

            if (!$driver) {
                continue;
            }

            $tripCar = null;

            if ($order->car_id) {
                $tripCar = $cars->get($order->car_id);
            }

            if (!$tripCar && $driver->current_car_id) {
                $tripCar = $cars->get($driver->current_car_id);
            }

            if ($tripCar) {
                $driver->setRelation('car', $tripCar);
            }

            if ($order->driver_id && in_array($order->status, $activeStatuses, true)) {
                $location = $locations->get($order->driver_id);

                if ($location) {
                    $order->setAttribute('driver_location', [
                        'lat' => (float) $location->lat,
                        'lng' => (float) $location->lng,
                    ]);
                }
            }
        }
    }

    public function newOrderUserIndex()
    {
        $user = Auth::user();
    
        $this->logOrderActivity('NEW_ORDER_USER_INDEX', [
            'user_id' => $user ? $user->id : null
        ], $user);

        if (!$user) {
            $this->logOrderActivity('NEW_ORDER_USER_INDEX_FAILED', ['reason' => 'Unauthenticated']);
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
    
        // Выборка для «Новых заказов» водителя: свободные pending для всех
        // + только свои searching - персональные предложения диспетчера,
        // ожидающие ответа этого водителя. Принятые и активные заказы сюда
        // не входят: их отдаёт index() (GET /api/orders), иначе принятый
        // заказ после перезагрузки списка снова падал в «Новые заказы».
        $orders = Order::where(function ($q) use ($user) {
                $q->where('status', 'pending')
                  ->orWhere(function ($q2) use ($user) {
                      $q2->where('driver_id', $user->id)
                         ->where('status', 'searching');
                  });
            })
            ->orderBy('id', 'desc')
            ->get();

        $this->logOrderActivity('NEW_ORDER_USER_INDEX_SUCCESS', [
            'orders_count' => $orders->count()
        ], $user);

        event(new OrderCreated());
    
        return response()->json(['data' => $orders]);
    }

    public function addDriving(Request $request)
    {
        $user = Auth::user();

        $this->logOrderActivity('ADD_DRIVING_START', [
            'order_id' => $request->input('orderId')
        ], $user);

        if (!$user) {
            $this->logOrderActivity('ADD_DRIVING_FAILED', ['reason' => 'Unauthenticated']);
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $order = Order::find($request->input('orderId'));

        if (!$order) {
            $this->logOrderActivity('ADD_DRIVING_ORDER_NOT_FOUND', [
                'order_id' => $request->input('orderId')
            ], $user);
            return response()->json(['status' => false, 'message' => 'Order not found'], 404);
        }

        // Повторное принятие своего активного заказа: заказ уже у этого
        // водителя - успешный идемпотентный ответ, состояние не меняем.
        if ((int) $order->driver_id === (int) $user->id
            && in_array($order->status, [Order::STATUS_ASSIGNED, Order::STATUS_ON_PLACE, Order::STATUS_IN_PROGRESS], true)) {
            return response()->json(['status' => true]);
        }

        // Один водитель - один активный заказ: пока есть незавершённая
        // поездка, другой заказ не выдаём.
        if ($this->driverHasActiveOrder($user, $order)) {
            $this->logOrderActivity('ADD_DRIVING_HAS_ACTIVE_ORDER', [
                'order_id' => $order->id,
                'driver_id' => $user->id
            ], $user);
            return response()->json(['status' => false, 'message' => 'У вас уже есть активный заказ']);
        }

        if (!$this->applyBoundCar($order, $request)) {
            return response()->json(['status' => false, 'message' => 'Car not found'], 422);
        }

        // Водитель принимает заказ, который диспетчер назначил лично ему:
        // это ответ на персональное предложение. Водитель остаётся тот же,
        // меняется только статус; OrderSentClose не нужен - предложение
        // персональное, другим водителям оно не уходило.
        if ((int) $order->driver_id === (int) $user->id && $order->status === Order::STATUS_SEARCHING) {
            $order->status = 'assigned';
            $order->save();

            $this->logOrderActivity('ADD_DRIVING_OWN_OFFER_SUCCESS', [
                'order_id' => $order->id,
                'driver_id' => $user->id,
                'status' => $order->status
            ], $user);

            event(new OrderCreated());
            event(new OrderStatusChanged($order, 'driver_accepted')); // Добавлено RAY

            if($order->client_id){
                event(new UserOrderSent($order->client_id));
                $this->logOrderActivity('ADD_DRIVING_CLIENT_NOTIFIED', [
                    'client_id' => $order->client_id,
                    'order_id' => $order->id
                ], $user);
            }

            return response()->json(['status' => true]);
        }

        if ($order->driver_id) {
            $this->logOrderActivity('ADD_DRIVING_ALREADY_ASSIGNED', [
                'order_id' => $order->id,
                'current_driver_id' => $order->driver_id,
                'attempted_driver_id' => $user->id
            ], $user);
            return response()->json(['status' => false, 'message' => 'Order already assigned']);
        }
        
        $order->driver_id = $user->id;
        $order->status = 'assigned';
        $order->save();

        $this->logOrderActivity('ADD_DRIVING_SUCCESS', [
            'order_id' => $order->id,
            'driver_id' => $user->id,
            'status' => $order->status
        ], $user);

        event(new OrderSentClose($order,$user->id));
        event(new OrderCreated());
        event(new OrderStatusChanged($order, 'driver_accepted')); // Добавлено RAY

        if($order->client_id){
            event(new UserOrderSent($order->client_id));
            $this->logOrderActivity('ADD_DRIVING_CLIENT_NOTIFIED', [
                'client_id' => $order->client_id,
                'order_id' => $order->id
            ], $user);
        }
        
        return response()->json(['status' => true]);
    }

    public function closeDriving(Request $request)
    {
        $user = Auth::user();

        $this->logOrderActivity('CLOSE_DRIVING_START', [
            'order_id' => $request->input('orderId'),
            'zakaz' => $request->input('zakaz'),
            'duration' => $request->input('time')
        ], $user);

        if (!$user) {
            $this->logOrderActivity('CLOSE_DRIVING_FAILED', ['reason' => 'Unauthenticated']);
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $order = Order::find($request->input('orderId'));
        
        if (!$order) {
            $this->logOrderActivity('CLOSE_DRIVING_ORDER_NOT_FOUND', [
                'order_id' => $request->input('orderId')
            ], $user);
            return response()->json(['status' => false, 'message' => 'Order not found'], 404);
        }
        
        $old_status = $order->status;
        $order->status = $request->input('zakaz');
        $order->duration = $request->input('time');
        $order->save();

        $this->logOrderActivity('CLOSE_DRIVING_SUCCESS', [
            'order_id' => $order->id,
            'old_status' => $old_status,
            'new_status' => $order->status,
            'duration' => $order->duration,
            'driver_id' => $user->id,
            'client_id' => $order->client_id
        ], $user);

        event(new OrderCreated());
        event(new OrderStatusChanged($order, 'driver_closed')); // Добавлено RAY

        if($request->input('zakaz') == 'completed'){
            $this->logOrderActivity('CLOSE_DRIVING_COMPLETED', [
                'order_id' => $order->id
            ], $user);
            return response()->json(['status' => true]);
        }else{
            // У заказа может не быть клиента (заказ «от борта» или клиент удалён) -
            // уведомлять некого, а конструктор события требует int.
            if ($order->client_id) {
                event(new UserOrderUpdate($order->client_id,'Заказ отменен'));
            }
            $this->logOrderActivity('CLOSE_DRIVING_CANCELED', [
                'order_id' => $order->id,
                'reason' => $request->input('zakaz')
            ], $user);
            // Запрошенный статус уже сохранён выше, поэтому отвечаем
            // status=true, как и за завершение: для приложения
            // status=false значит «закрытие отклонено». Настоящие отказы
            // у этого метода - только 401 и 404 выше.
            return response()->json(['status' => true]);
        }
    }

    public function closeDrivingPrice(Request $request)
    {
        $user = Auth::user();

        $this->logOrderActivity('CLOSE_DRIVING_PRICE_START', [
            'order_id' => $request->input('orderId'),
            'price_received' => $request->has('price') ? $request->input('price') : 'NOT_PROVIDED'
        ], $user);

        if (!$user) {
            $this->logOrderActivity('CLOSE_DRIVING_PRICE_FAILED', ['reason' => 'Unauthenticated']);
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        // Цена необязательна, но если передана - только число и не ниже
        // нуля. Раньше валидации не было: строка "null" из приложения
        // писалась прямо в decimal-колонку, и strict-режим MySQL отвечал
        // ошибкой 500 вместо внятного отказа. Теперь мусор получает 422,
        // а заказ остаётся нетронутым.
        $request->validate([
            'price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $order = Order::find($request->input('orderId'));

        if (!$order) {
            $this->logOrderActivity('CLOSE_DRIVING_PRICE_ORDER_NOT_FOUND', [
                'order_id' => $request->input('orderId')
            ], $user);
            return response()->json(['status' => false, 'message' => 'Order not found'], 404);
        }
        
        // Обновляем цену только если она передана в запросе
        if ($request->has('price')) {
            $old_price = $order->price;
            $order->price = $request->input('price');
            $order->save();
            
            $this->logOrderActivity('CLOSE_DRIVING_PRICE_UPDATED', [
                'order_id' => $order->id,
                'old_price' => $old_price,
                'new_price' => $order->price,
                'driver_id' => $user->id,
                'client_id' => $order->client_id
            ], $user);
        } else {
            $this->logOrderActivity('CLOSE_DRIVING_PRICE_SKIPPED', [
                'order_id' => $order->id,
                'reason' => 'Price not provided in request'
            ], $user);
        }

        // У заказа может не быть клиента (заказ «от борта» или клиент удалён) -
        // уведомлять некого, а конструктор события требует int.
        if ($order->client_id) {
            event(new UserOrderUpdate($order->client_id,'Заказ успешно завершен'));
        }

        $this->logOrderActivity('CLOSE_DRIVING_PRICE_COMPLETED', [
            'order_id' => $order->id
        ], $user);

        return response()->json(['status' => true]);
    }

    public function newOrderUser(Request $request)
    {
        $user = Auth::user();

        $this->logOrderActivity('NEW_ORDER_USER_START', [
            'toAddress' => $request->input('toAddress'),
            'fromAddress' => $request->input('fromAddress'),
        ], $user);

        if (!$user) {
            $this->logOrderActivity('NEW_ORDER_USER_FAILED', ['reason' => 'Unauthenticated']);
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $toAddress = $request->input('toAddress');

        if (!$toAddress) {
            return response()->json(['status' => false, 'message' => 'Поле toAddress не заполнено'], 400);
        }

        // The app sends real coordinates for both points; the server-side
        // geocoder is only a fallback for older app versions.
        $toLat = $request->input('toLat');
        $toLng = $request->input('toLon');

        if (!is_numeric($toLat) || !is_numeric($toLng)) {
            $point = $this->geocodePoint($toAddress);

            if ($point === null) {
                return response()->json(['status' => false, 'message' => 'Координаты не найдены по адресу'], 404);
            }

            $toLat = $point['lat'];
            $toLng = $point['lng'];
        }

        $fromAddress = $request->input('fromAddress');
        $fromLat = $request->input('fromLat');
        $fromLng = $request->input('fromLon');

        if ($fromAddress === "Моё местоположение") {
            $fromAddress = "От клиента";
        }

        if (!is_numeric($fromLat) || !is_numeric($fromLng)) {
            $point = $this->geocodePoint((string) $fromAddress);

            if ($point === null) {
                return response()->json(['status' => false, 'message' => 'Координаты не найдены по адресу отправления'], 404);
            }

            $fromLat = $point['lat'];
            $fromLng = $point['lng'];
        }

        $order = Order::create([
            'client_id' => $user->id,
            'client_first_name' => $user->first_name,
            'client_last_name' => $user->last_name,
            'client_phone' => $user->phone,
            'from_address' => $fromAddress,
            'from_lat' => (float) $fromLat,
            'from_lng' => (float) $fromLng,
            'to_address' => $toAddress,
            'to_lat' => (float) $toLat,
            'to_lng' => (float) $toLng,
            'distance_to_client' => 0,
            'time_to_client' => 0,
            'distance' => max(0, (int) $request->input('distance', 0)),
            'duration' => max(0, (int) $request->input('duration', 0)),
            'price' => null,
            'tariff' => $request->input('tariff'),
            // Источник заказа из приложения проставляет сервер,
            // входное значение приложения не принимается
            'source' => Order::SOURCE_APP,
        ]);

        $this->logOrderActivity('NEW_ORDER_USER_CREATED', [
            'order_id' => $order->id,
            'client_id' => $user->id,
            'from_coordinates' => "$fromLat, $fromLng",
            'to_coordinates' => "$toLat, $toLng",
        ], $user);

        event(new OrderCreated());
        event(new OrderSentNewDriving('Поступил новый заказ'));

        return response()->json(['status' => true]);
    }

    /**
     * Geocode an address via Yandex; returns ['lat' => ..., 'lng' => ...]
     * or null when the key is missing or nothing was found.
     */
    private function geocodePoint(string $address): ?array
    {
        $key = config('services.yandex.geocoder_key');

        if (!$key || $address === '') {
            return null;
        }

        $response = Http::get('https://geocode-maps.yandex.ru/1.x/', [
            'apikey' => $key,
            'format' => 'json',
            'geocode' => $address,
            'lang' => 'ru_RU',
            'results' => 1,
        ]);

        if ($response->failed()) {
            return null;
        }

        $pos = $response->json('response.GeoObjectCollection.featureMember.0.GeoObject.Point.pos');

        if (!$pos) {
            return null;
        }

        [$lng, $lat] = explode(' ', $pos);

        return ['lat' => (float) $lat, 'lng' => (float) $lng];
    }

    public function closeOrderUser(Request $request)
    {
        $user = Auth::user();

        $this->logOrderActivity('CLOSE_ORDER_USER_START', [
            'order_id' => $request->input('orderId')
        ], $user);

        if (!$user) {
            $this->logOrderActivity('CLOSE_ORDER_USER_FAILED', ['reason' => 'Unauthenticated']);
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $order = Order::find($request->input('orderId'));

        if (!$order) {
            $this->logOrderActivity('CLOSE_ORDER_USER_ORDER_NOT_FOUND', [
                'order_id' => $request->input('orderId')
            ], $user);
            return response()->json(['status' => false, 'message' => 'Order not found'], 404);
        }

        event(new OrderCreated());

        if($order->client_id == $user->id){
            $old_status = $order->status;
            $order->status = 'canceled';
            $order->save();
            
            $this->logOrderActivity('CLOSE_ORDER_USER_SUCCESS', [
                'order_id' => $order->id,
                'client_id' => $user->id,
                'old_status' => $old_status,
                'new_status' => $order->status
            ], $user);
            
            event(new OrderStatusChanged($order, 'client_canceled')); // Добавлено RAY
            event(new OrderSentNewDriving('Изменения в списке заказов'));
            return response()->json(['status' => true]);
        } else {
            $this->logOrderActivity('CLOSE_ORDER_USER_UNAUTHORIZED', [
                'order_id' => $order->id,
                'user_id' => $user->id,
                'client_id' => $order->client_id
            ], $user);
            return response()->json(['status' => false]);
        }
    }

    public function closeOrderUserDriving(Request $request)
    {
        $user = Auth::user();

        $this->logOrderActivity('CLOSE_ORDER_USER_DRIVING_START', [
            'order_id' => $request->input('orderId')
        ], $user);

        if (!$user) {
            $this->logOrderActivity('CLOSE_ORDER_USER_DRIVING_FAILED', ['reason' => 'Unauthenticated']);
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $order = Order::find($request->input('orderId'));

        if (!$order) {
            $this->logOrderActivity('CLOSE_ORDER_USER_DRIVING_ORDER_NOT_FOUND', [
                'order_id' => $request->input('orderId')
            ], $user);
            return response()->json(['status' => false, 'message' => 'Order not found'], 404);
        }

        $old_status = $order->status;
        $order->status = 'canceled';
        $order->save();

        $this->logOrderActivity('CLOSE_ORDER_USER_DRIVING_SUCCESS', [
            'order_id' => $order->id,
            'driver_id' => $user->id,
            'client_id' => $order->client_id,
            'old_status' => $old_status,
            'new_status' => $order->status
        ], $user);
      
        // У заказа может не быть клиента (заказ «от борта» или клиент удалён) -
        // уведомлять некого, а конструктор события требует int.
        if ($order->client_id) {
            event(new UserOrderUpdate($order->client_id,'Заказ отменен'));
        }
        event(new OrderCreated());
        event(new OrderStatusChanged($order, 'driver_canceled')); // Добавлено RAY

        return response()->json(['status' => true]);
    }

    public function acceptOrderByDriver(Request $request)
    {
        $user = Auth::user();

        $this->logOrderActivity('ACCEPT_ORDER_BY_DRIVER_START', [
            'order_id' => $request->input('orderId')
        ], $user);

        if (!$user) {
            $this->logOrderActivity('ACCEPT_ORDER_BY_DRIVER_FAILED', ['reason' => 'Unauthenticated']);
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $order = Order::find($request->input('orderId'));

        if (!$order) {
            $this->logOrderActivity('ACCEPT_ORDER_BY_DRIVER_ORDER_NOT_FOUND', [
                'order_id' => $request->input('orderId')
            ], $user);
            return response()->json(['status' => false, 'message' => 'Order not found'], 404);
        }

        // Повторное принятие своего активного заказа: заказ уже у этого
        // водителя - успешный идемпотентный ответ, состояние не меняем.
        if ((int) $order->driver_id === (int) $user->id
            && in_array($order->status, [Order::STATUS_ASSIGNED, Order::STATUS_ON_PLACE, Order::STATUS_IN_PROGRESS], true)) {
            return response()->json([
                'status' => true,
                'order' => $order
            ]);
        }

        // Один водитель - один активный заказ: пока есть незавершённая
        // поездка, другой заказ не выдаём.
        if ($this->driverHasActiveOrder($user, $order)) {
            $this->logOrderActivity('ACCEPT_ORDER_BY_DRIVER_HAS_ACTIVE_ORDER', [
                'order_id' => $order->id,
                'driver_id' => $user->id
            ], $user);
            return response()->json(['status' => false, 'message' => 'У вас уже есть активный заказ']);
        }

        if (!$this->applyBoundCar($order, $request)) {
            return response()->json(['status' => false, 'message' => 'Car not found'], 422);
        }

        // Водитель принимает заказ, который диспетчер назначил лично ему:
        // это ответ на персональное предложение. Водитель остаётся тот же,
        // меняется только статус; OrderSentClose не нужен - предложение
        // персональное, другим водителям оно не уходило.
        if ((int) $order->driver_id === (int) $user->id && $order->status === Order::STATUS_SEARCHING) {
            $order->status = 'assigned';
            $order->save();

            $this->logOrderActivity('ACCEPT_ORDER_BY_DRIVER_OWN_OFFER_SUCCESS', [
                'order_id' => $order->id,
                'driver_id' => $user->id,
                'client_id' => $order->client_id,
                'status' => $order->status
            ], $user);

            event(new OrderCreated());
            event(new OrderStatusChanged($order, 'driver_accepted')); // Добавлено RAY

            if($order->client_id){
                event(new UserOrderSent($order->client_id));
                $this->logOrderActivity('ACCEPT_ORDER_BY_DRIVER_CLIENT_NOTIFIED', [
                    'client_id' => $order->client_id,
                    'order_id' => $order->id
                ], $user);
            }

            return response()->json([
                'status' => true,
                'order' => $order
            ]);
        }

        if ($order->driver_id) {
            $this->logOrderActivity('ACCEPT_ORDER_BY_DRIVER_ALREADY_ASSIGNED', [
                'order_id' => $order->id,
                'current_driver_id' => $order->driver_id,
                'attempted_driver_id' => $user->id
            ], $user);
            return response()->json(['status' => false, 'message' => 'Order already assigned']);
        }
        
        $order->driver_id = $user->id;
        $order->status = 'assigned';
        $order->save();

        $this->logOrderActivity('ACCEPT_ORDER_BY_DRIVER_SUCCESS', [
            'order_id' => $order->id,
            'driver_id' => $user->id,
            'client_id' => $order->client_id,
            'status' => $order->status
        ], $user);

        event(new OrderSentClose($order,$user->id));
        event(new OrderCreated());
        event(new OrderStatusChanged($order, 'driver_accepted')); // Добавлено RAY

        if($order->client_id){
            event(new UserOrderSent($order->client_id));
            $this->logOrderActivity('ACCEPT_ORDER_BY_DRIVER_CLIENT_NOTIFIED', [
                'client_id' => $order->client_id,
                'order_id' => $order->id
            ], $user);
        }
        
        return response()->json([
            'status' => true,
            'order' => $order
        ]);
    }

    // Водитель отказался от предложенного заказа.
    // Свободный заказ не меняется - фиксируем только факт отказа (лог
    // активности). Если заказ был назначен диспетчером лично этому водителю,
    // назначение снимается (заказ остаётся в поиске) и диспетчер сразу
    // получает сигнал AdminAlert, что нужен новый водитель.
    public function declineOrderByDriver(Request $request)
    {
        $user = Auth::user();

        $this->logOrderActivity('DECLINE_ORDER_BY_DRIVER_START', [
            'order_id' => $request->input('orderId')
        ], $user);

        if (!$user) {
            $this->logOrderActivity('DECLINE_ORDER_BY_DRIVER_FAILED', ['reason' => 'Unauthenticated']);
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $order = Order::find($request->input('orderId'));

        if (!$order) {
            $this->logOrderActivity('DECLINE_ORDER_BY_DRIVER_ORDER_NOT_FOUND', [
                'order_id' => $request->input('orderId')
            ], $user);
            return response()->json(['status' => false, 'message' => 'Order not found'], 404);
        }

        // Отказ от персонального предложения диспетчера: снимаем назначение,
        // статус не трогаем (заказ остаётся в поиске). Метку предложения
        // driver_offered_at СОХРАНЯЕМ: это свидетельство «заказ предлагался»,
        // по ней панель «Требуют внимания» отличает отказанный заказ от
        // свежего broadcast-заказа (Order::scopeDeclinedByDriver). Метку
        // сбрасывает только диспетчер - при ручном снятии водителя или
        // переназначении в админке. Предложение другим водителям само не
        // рассылается - переназначение делает диспетчер, для этого ему и
        // уходит сигнал.
        if ((int) $order->driver_id === (int) $user->id && $order->status === Order::STATUS_SEARCHING) {
            $alert = AdminAlert::driverDeclined($order);

            $order->driver_id = null;
            $order->save();

            // Статус при отказе не меняется, поэтому Observer
            // молчит: сигнал клиенту шлём сами, иначе карточка
            // заказа в приложении навсегда показывает
            // отказавшегося водителя.
            if ($order->client_id) {
                event(new ClientOrderUpdated($order));
            }

            $this->logOrderActivity('DECLINE_ORDER_BY_DRIVER_SUCCESS', [
                'order_id' => $order->id,
                'driver_id' => $user->id,
                'client_id' => $order->client_id,
                'status' => $order->status
            ], $user);

            event(new OrderStatusChanged($order, 'driver_declined'));
            event($alert);

            return response()->json(['status' => true]);
        }

        if ($order->driver_id) {
            $this->logOrderActivity('DECLINE_ORDER_BY_DRIVER_ALREADY_ASSIGNED', [
                'order_id' => $order->id,
                'current_driver_id' => $order->driver_id,
                'attempted_driver_id' => $user->id
            ], $user);
            return response()->json(['status' => false, 'message' => 'Order already assigned']);
        }

        $this->logOrderActivity('DECLINE_ORDER_BY_DRIVER_SUCCESS', [
            'order_id' => $order->id,
            'driver_id' => $user->id,
            'client_id' => $order->client_id,
            'status' => $order->status
        ], $user);

        return response()->json(['status' => true]);
    }

    // Добавлено RAY - водитель на месте
    public function onPlace(Request $request)
    {
        $user  = Auth::user();
        $order = Order::find($request->input('orderId'));

        if (!$order || $order->driver_id !== $user->id) {
            return response()->json(['status' => false, 'message' => 'Forbidden'], 403);
        }

        $order->status = 'on_place';
        $order->save();

        event(new OrderStatusChanged($order, 'driver_on_place'));

        return response()->json(['status' => true]);
    }

    // Добавлено RAY - поездка началась
    public function startRide(Request $request)
    {
        $user  = Auth::user();
        $order = Order::find($request->input('orderId'));

        if (!$order || $order->driver_id !== $user->id) {
            return response()->json(['status' => false, 'message' => 'Forbidden'], 403);
        }

        if (!$this->applyBoundCar($order, $request)) {
            return response()->json(['status' => false, 'message' => 'Car not found'], 422);
        }

        $order->status = 'in_progress';
        $order->save();

        event(new OrderStatusChanged($order, 'ride_started'));

        return response()->json(['status' => true]);
    }

    // Есть ли у водителя другой активный заказ (назначен, на месте или
    // в поездке). $except - заказ, который принимают прямо сейчас: он сам
    // препятствием не считается.
    private function driverHasActiveOrder(User $user, Order $except): bool
    {
        return Order::where('driver_id', $user->id)
            ->whereIn('status', [Order::STATUS_ASSIGNED, Order::STATUS_ON_PLACE, Order::STATUS_IN_PROGRESS])
            ->where('id', '!=', $except->id)
            ->exists();
    }

    // Привязка авто при установке: водительское приложение шлёт car_id
    // привязанного устройства. Пишем в заказ, только если авто ещё не
    // задано: привязка жёсткая, первое авто заказа не перезаписывается.
    // false - передан несуществующий car_id, запрос надо отклонить.
    private function applyBoundCar(Order $order, Request $request): bool
    {
        $carId = $request->input('car_id');

        if ($carId === null || $carId === '') {
            return true;
        }

        if (!Car::whereKey($carId)->exists()) {
            return false;
        }

        if (!$order->car_id) {
            $order->car_id = (int) $carId;
        }

        return true;
    }
}