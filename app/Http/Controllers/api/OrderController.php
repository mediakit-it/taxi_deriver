<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Events\OrderSentClose;
use App\Events\UserOrderSent;
use App\Events\OrderSentNewDriving;
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
            $this->logOrderActivity('INDEX_ORDERS_CLIENT', [
                'client_id' => $user->id,
                'orders_count' => $orders->count()
            ], $user);
        }

        return response()->json(['data' => $orders]);
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
    
        // Добавлено RAY - расширенная выборка: pending + свои активные заказы водителя
        $orders = Order::where(function ($q) use ($user) {
                $q->where('status', 'pending')
                  ->orWhere(function ($q2) use ($user) {
                      $q2->where('driver_id', $user->id)
                         ->whereIn('status', [
                             'searching',
                             'assigned',
                             'on_place',
                             'in_progress',
                         ]);
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
            event(new UserOrderUpdate($order->client_id,'Заказ отменен'));
            $this->logOrderActivity('CLOSE_DRIVING_CANCELED', [
                'order_id' => $order->id,
                'reason' => $request->input('zakaz')
            ], $user);
            return response()->json(['status' => false]);
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

        event(new UserOrderUpdate($order->client_id,'Заказ успешно завершен'));

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
      
        event(new UserOrderUpdate($order->client_id,'Заказ отменен'));
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

        // Отказ от персонального предложения диспетчера: снимаем назначение
        // и метку предложения, статус не трогаем (заказ остаётся в поиске).
        // Предложение другим водителям само не рассылается - переназначение
        // делает диспетчер, для этого ему и уходит сигнал.
        if ((int) $order->driver_id === (int) $user->id && $order->status === Order::STATUS_SEARCHING) {
            $alert = AdminAlert::driverDeclined($order);

            $order->driver_id = null;
            $order->driver_offered_at = null;
            $order->save();

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

        $order->status = 'in_progress';
        $order->save();

        event(new OrderStatusChanged($order, 'ride_started'));

        return response()->json(['status' => true]);
    }
}