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

class OrderController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        if($user->role=='driving')
            $orders = Order::where('driver_id',$user->id)->whereNotNull('driver_id')->orderBy('id','desc')->get();
        else
            $orders = Order::where('client_id',$user->id)->orderBy('id','desc')->with('driver')->get();

        return response()->json(['data' => $orders]);
    }

    public function newOrderUserIndex()
    {
        $user = Auth::user();
    
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
    
        $orders = Order::whereNotNull('client_id')
            ->where('status', 'pending')
            ->orderBy('id', 'desc')
            ->get();

            event(new OrderCreated());
    
        return response()->json(['data' => $orders]);
    }

    //дописать пуш уведомление на закрытие у всех остальных окон заказа 
    public function addDriving(Request $request)
    {

        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $order = Order::find($request->input('orderId'));

        if (!$order) {
            return response()->json(['status' => false, 'message' => 'Order not found'], 404);
        }
        
        if ($order->driver_id) {
            return response()->json(['status' => false, 'message' => 'Order already assigned']);
        }
        
        $order->driver_id = $user->id;
        $order->status = 'assigned';
        $order->save();

        event(new OrderSentClose($order,$user->id));
        event(new OrderCreated());


        if($order->client_id){
            event(new UserOrderSent($order->client_id));
            //дописать обновление и уведомление что новый заказ
        }
        
        return response()->json(['status' => true]);
        
    }

    public function closeDriving(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $order = Order::find($request->input('orderId'));
        

        if (!$order) {
            return response()->json(['status' => false, 'message' => 'Order not found'], 404);
        }
        
        
        $order->status = $request->input('zakaz');
        $order->duration = $request->input('time');
        $order->save();
        event(new OrderCreated());

        if($request->input('zakaz') == 'completed'){
            return response()->json(['status' => true]);
        }else{
            event(new UserOrderUpdate($order->client_id,'Заказ отменен'));
            return response()->json(['status' => false]);
        }
    }

    public function closeDrivingPrice(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $order = Order::find($request->input('orderId'));

        if (!$order) {
            return response()->json(['status' => false, 'message' => 'Order not found'], 404);
        }
        
        
        $order->price = $request->input('price');
        $order->save();

        event(new UserOrderUpdate($order->client_id,'Заказ успешно завершен'));
        // event(new OrderCreated());

        return response()->json(['status' => true]);
    }

    public function newOrderUser(Request $request)
    {

        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }



        $toAddress = $request->input('toAddress');

    if (!$toAddress) {
        return response()->json(['status' => false, 'message' => 'Поле toAddress не заполнено'], 400);
    }

    // Запрос к Яндекс Геокодеру для получения координат
    $response = Http::get('https://geocode-maps.yandex.ru/1.x/', [
        'apikey' => 'ad38720a-e8c9-443c-b454-1f4d2499ad78', // твой ключ
        'format' => 'json',
        'geocode' => $toAddress,
        'lang' => 'ru_RU',
        'results' => 1,
    ]);

    if ($response->failed()) {
        return response()->json(['status' => false, 'message' => 'Ошибка при получении координат'], 500);
    }

    $json = $response->json();

    $featureMember = $json['response']['GeoObjectCollection']['featureMember'] ?? [];

    if (count($featureMember) === 0) {
        return response()->json(['status' => false, 'message' => 'Координаты не найдены по адресу'], 404);
    }

    $pos = $featureMember[0]['GeoObject']['Point']['pos'] ?? null;

    if (!$pos) {
        return response()->json(['status' => false, 'message' => 'Координаты отсутствуют в ответе'], 404);
    }

    [$longitude, $latitude] = explode(' ', $pos);


    $fromAddress = $request->input('fromAddress');
    $fromLat = $request->input('fromLat');
    $fromLon = $request->input('fromLon');
    if($fromAddress=="Моё местоположение"){
        $fromAddress = "От клиента";
    }else{
        $fromResponse = Http::get('https://geocode-maps.yandex.ru/1.x/', [
            'apikey' => 'ad38720a-e8c9-443c-b454-1f4d2499ad78',
            'format' => 'json',
            'geocode' => $fromAddress,
            'lang' => 'ru_RU',
            'results' => 1,
        ]);
    
        if ($fromResponse->failed()) {
            return response()->json(['status' => false, 'message' => 'Ошибка при получении координат (fromAddress)'], 500);
        }
    
        $fromJson = $fromResponse->json();
        $fromFeatureMember = $fromJson['response']['GeoObjectCollection']['featureMember'] ?? [];
    
        if (count($fromFeatureMember) === 0) {
            return response()->json(['status' => false, 'message' => 'Координаты не найдены по адресу отправления'], 404);
        }
    
        $fromPos = $fromFeatureMember[0]['GeoObject']['Point']['pos'] ?? null;
    
        if (!$fromPos) {
            return response()->json(['status' => false, 'message' => 'Координаты отсутствуют в ответе (откуда)'], 404);
        }
    
        [$fromLon, $fromLat] = explode(' ', $fromPos);
    }


    $order = Order::create(
        [
            'client_id'=>$user->id,
            'client_first_name'=>$user->first_name,
            'client_last_name'=>$user->last_name,
            'client_phone'=>$user->phone,
            'from_address'=>$fromAddress,
            'from_lat'=>$fromLon,
            'from_lng'=>$fromLat,
            'to_address'=>$toAddress,
            'to_lat'=>$longitude,
            'to_lng'=>$latitude,
            'distance_to_client'=>0,
            'time_to_client'=>0,
            'distance'=>0,
            'duration'=>0,
            'price'=>5,
        ]
    );


    \Log::info('Координаты по адресу: ' . $toAddress . ' — широта: ' . $latitude . ', долгота: ' . $longitude);


    // Здесь можно сохранить координаты в заказе, например:
    // $order->latitude = $latitude;
    // $order->longitude = $longitude;
    // $order->save();

    // Для примера просто возвращаем ответ с координатами
    event(new OrderCreated());

    if($order){
        event(new OrderSentNewDriving('Поступил новый заказ'));
        return response()->json([
            'status' => true,
        ]);
    }
    else{
        return response()->json([
            'status' => false,
        ]);
    }
    }

    public function closeOrderUser(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $order = Order::find($request->input('orderId'));

        if (!$order) {
            return response()->json(['status' => false, 'message' => 'Order not found'], 404);
        }
        event(new OrderCreated());

        if($order->client_id==$user->id){
            $order->status = 'canceled';
            $order->save();
            event(new OrderSentNewDriving('Изменения в списке заказов'));
            return response()->json(['status' => true]);
        }else{
            return response()->json(['status' => false]);
        }
    }

    public function closeOrderUserDriving(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $order = Order::find($request->input('orderId'));

        if (!$order) {
            return response()->json(['status' => false, 'message' => 'Order not found'], 404);
        }

            $order->status = 'canceled';
            $order->save();
          
            event(new UserOrderUpdate($order->client_id,'Заказ отменен'));
            event(new OrderCreated());

            return response()->json(['status' => true]);
    }


    public function acceptOrderByDriver(Request $request)
    {

        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $order = Order::find($request->input('orderId'));

        if (!$order) {
            return response()->json(['status' => false, 'message' => 'Order not found'], 404);
        }
        
        if ($order->driver_id) {
            return response()->json(['status' => false, 'message' => 'Order already assigned']);
        }
        
        $order->driver_id = $user->id;
        $order->status = 'assigned';
        $order->save();

        event(new OrderSentClose($order,$user->id));
        event(new OrderCreated());


        if($order->client_id){
            event(new UserOrderSent($order->client_id));
        }
        
        return response()->json([
            'status' => true,
            'order' => $order
        ]);
        
    }
}
