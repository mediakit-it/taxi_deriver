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
            'fromAddress' => $request->input('fromAddress')
        ], $user);

        if (!$user) {
            $this->logOrderActivity('NEW_ORDER_USER_FAILED', ['reason' => 'Unauthenticated']);
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $toAddress = $request->input('toAddress');

        if (!$toAddress) {
            $this->logOrderActivity('NEW_ORDER_USER_ADDRESS_MISSING', [
                'reason' => 'toAddress not provided'
            ], $user);
            return response()->json(['status' => false, 'message' => 'Поле toAddress не заполнено'], 400);
        }

        // Запрос к Яндекс Геокодеру для получения координат
        $this->logOrderActivity('NEW_ORDER_USER_YANDEX_REQUEST', [
            'address' => $toAddress,
            'action' => 'geocoding_to_address'
        ], $user);

        $response = Http::get('https://geocode-maps.yandex.ru/1.x/', [
            'apikey' => 'ad38720a-e8c9-443c-b454-1f4d2499ad78',
            'format' => 'json',
            'geocode' => $toAddress,
            'lang' => 'ru_RU',
            'results' => 1,
        ]);

        if ($response->failed()) {
            $this->logOrderActivity('NEW_ORDER_USER_YANDEX_ERROR', [
                'address' => $toAddress,
                'error' => 'Yandex API request failed'
            ], $user);
            return response()->json(['status' => false, 'message' => 'Ошибка при получении координат'], 500);
        }

        $json = $response->json();
        $featureMember = $json['response']['GeoObjectCollection']['featureMember'] ?? [];

        if (count($featureMember) === 0) {
            $this->logOrderActivity('NEW_ORDER_USER_COORDINATES_NOT_FOUND', [
                'address' => $toAddress
            ], $user);
            return response()->json(['status' => false, 'message' => 'Координаты не найдены по адресу'], 404);
        }

        $pos = $featureMember[0]['GeoObject']['Point']['pos'] ?? null;

        if (!$pos) {
            $this->logOrderActivity('NEW_ORDER_USER_POSITION_MISSING', [
                'address' => $toAddress
            ], $user);
            return response()->json(['status' => false, 'message' => 'Координаты отсутствуют в ответе'], 404);
        }

        [$longitude, $latitude] = explode(' ', $pos);

        $fromAddress = $request->input('fromAddress');
        $fromLat = $request->input('fromLat');
        $fromLon = $request->input('fromLon');
        
        if($fromAddress=="Моё местоположение"){
            $fromAddress = "От клиента";
            $this->logOrderActivity('NEW_ORDER_USER_FROM_LOCATION', [
                'user_location' => 'Клиентское местоположение'
            ], $user);
        } else {
            $this->logOrderActivity('NEW_ORDER_USER_YANDEX_REQUEST', [
                'address' => $fromAddress,
                'action' => 'geocoding_from_address'
            ], $user);

            $fromResponse = Http::get('https://geocode-maps.yandex.ru/1.x/', [
                'apikey' => 'ad38720a-e8c9-443c-b454-1f4d2499ad78',
                'format' => 'json',
                'geocode' => $fromAddress,
                'lang' => 'ru_RU',
                'results' => 1,
            ]);
        
            if ($fromResponse->failed()) {
                $this->logOrderActivity('NEW_ORDER_USER_YANDEX_ERROR', [
                    'address' => $fromAddress,
                    'error' => 'Yandex API request failed for fromAddress'
                ], $user);
                return response()->json(['status' => false, 'message' => 'Ошибка при получении координат (fromAddress)'], 500);
            }
        
            $fromJson = $fromResponse->json();
            $fromFeatureMember = $fromJson['response']['GeoObjectCollection']['featureMember'] ?? [];
        
            if (count($fromFeatureMember) === 0) {
                $this->logOrderActivity('NEW_ORDER_USER_COORDINATES_NOT_FOUND', [
                    'address' => $fromAddress
                ], $user);
                return response()->json(['status' => false, 'message' => 'Координаты не найдены по адресу отправления'], 404);
            }
        
            $fromPos = $fromFeatureMember[0]['GeoObject']['Point']['pos'] ?? null;
        
            if (!$fromPos) {
                $this->logOrderActivity('NEW_ORDER_USER_POSITION_MISSING', [
                    'address' => $fromAddress
                ], $user);
                return response()->json(['status' => false, 'message' => 'Координаты отсутствуют в ответе (откуда)'], 404);
            }
        
            [$fromLon, $fromLat] = explode(' ', $fromPos);
        }

        $order = Order::create([
            'client_id' => $user->id,
            'client_first_name' => $user->first_name,
            'client_last_name' => $user->last_name,
            'client_phone' => $user->phone,
            'from_address' => $fromAddress,
            'from_lat' => $fromLon,
            'from_lng' => $fromLat,
            'to_address' => $toAddress,
            'to_lat' => $longitude,
            'to_lng' => $latitude,
            'distance_to_client' => 0,
            'time_to_client' => 0,
            'distance' => 0,
            'duration' => 0,
            'price' => null,
        ]);

        $this->logOrderActivity('NEW_ORDER_USER_CREATED', [
            'order_id' => $order->id,
            'client_id' => $user->id,
            'from_address' => $fromAddress,
            'to_address' => $toAddress,
            'from_coordinates' => "$fromLat, $fromLon",
            'to_coordinates' => "$latitude, $longitude"
        ], $user);

        \Log::info('Координаты по адресу: ' . $toAddress . ' — широта: ' . $latitude . ', долгота: ' . $longitude);

        event(new OrderCreated());

        if($order){
            event(new OrderSentNewDriving('Поступил новый заказ'));
            $this->logOrderActivity('NEW_ORDER_USER_SUCCESS', [
                'order_id' => $order->id
            ], $user);
            return response()->json(['status' => true]);
        } else {
            $this->logOrderActivity('NEW_ORDER_USER_CREATION_FAILED', [
                'user_id' => $user->id
            ], $user);
            return response()->json(['status' => false]);
        }
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