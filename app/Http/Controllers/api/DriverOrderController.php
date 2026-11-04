<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\DriverCurbOrderRequest;
use App\Models\Order;
use Illuminate\Http\JsonResponse;

// Заказы, которые водитель создаёт сам. Сейчас один сценарий -
// «от борта»: пассажир сел с улицы, водитель заводит заказ из
// приложения, заказ сразу назначен на него и его авто, дальше
// поездка идёт обычным сценарием (на месте -> в дороге ->
// завершение с ценой). Диспетчер видит заказ в общем списке с
// источником «От борта»; настройка разрешённых источников
// («Функционал») этот сценарий не гейтит - она только про то, что
// может выбрать диспетчер в своей форме.
class DriverOrderController extends Controller
{
    public function store(DriverCurbOrderRequest $request): JsonResponse
    {
        $driver = $request->user();

        // Без привязанного на смену авто заказ завести нельзя: заказ
        // сразу назначается на авто, и диспетчер должен его видеть.
        if (!$driver->current_car_id) {
            return response()->json([
                'success' => false,
                'message' => 'Автомобиль не привязан. Укажите автомобиль перед входом и войдите снова.',
            ], 422);
        }

        // Точка подачи - текущие координаты водителя: приложение
        // шлёт их в запросе, если не прислало - последняя известная
        // геопозиция (driver_locations, одна строка на водителя).
        $fromLat = $request->validated('from_lat');
        $fromLng = $request->validated('from_lng');

        if ($fromLat === null || $fromLng === null) {
            $location = $driver->location;
            $fromLat = $location?->lat;
            $fromLng = $location?->lng;
        }

        if ($fromLat === null || $fromLng === null) {
            return response()->json([
                'success' => false,
                'message' => 'Не удалось определить точку подачи: нет текущих координат водителя.',
            ], 422);
        }

        // Клиента у такого заказа нет: client_id пуст, снимок клиента
        // не выдумываем (телефон - только если пассажир его назвал и
        // приложение прислало). Создание модели само шлёт сигнал
        // админке (OrderObserver::created -> AdminOrdersChanged).
        $order = Order::create([
            'client_id' => null,
            'client_phone' => $request->validated('phone'),
            'driver_id' => $driver->id,
            'car_id' => $driver->current_car_id,
            'from_address' => $request->validated('from_address'),
            'from_lat' => (float) $fromLat,
            'from_lng' => (float) $fromLng,
            'to_address' => $request->validated('to_address'),
            'to_lat' => (float) $request->validated('to_lat'),
            'to_lng' => (float) $request->validated('to_lng'),
            'distance_to_client' => 0,
            'time_to_client' => 0,
            'distance' => $request->validated('distance'),
            'duration' => $request->validated('duration'),
            'price' => null,
            'comment' => $request->validated('comment'),
            'status' => Order::STATUS_ASSIGNED,
            'source' => Order::SOURCE_CURB,
        ]);

        return response()->json([
            'success' => true,
            'order' => [
                'id' => $order->id,
                'status' => $order->status,
                'source' => $order->source,
                'driver_id' => $order->driver_id,
                'car_id' => $order->car_id,
                'from_address' => $order->from_address,
                'from_lat' => $order->from_lat,
                'from_lng' => $order->from_lng,
                'to_address' => $order->to_address,
                'to_lat' => $order->to_lat,
                'to_lng' => $order->to_lng,
            ],
        ], 201);
    }
}
