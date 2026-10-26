<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;

class DispatcherAlertController extends Controller
{
    /**
     * Заказы для панели «Требуют внимания»: назначенный водитель не
     * ответил вовремя (reason = no_response) или отказался от заказа
     * (reason = declined). Источник правды для панели диспетчерских
     * сигналов в админке: карточка заказа живёт в панели, пока заказ
     * в этом списке, и исчезает, когда диспетчер переназначит водителя.
     */
    public function overdue(): JsonResponse
    {
        $orders = Order::awaitingDriverResponse(Order::driverAcceptTimeout())
            ->with('driver')
            ->get()
            ->concat(Order::declinedByDriver()->get())
            ->sortBy('driver_offered_at')
            ->values();

        return response()->json(
            $orders->map(fn (Order $order) => [
                'order_id' => $order->id,
                // В списке «без ответа» водитель ещё назначен, в списке
                // отказов - уже снят (см. scopeDeclinedByDriver).
                'reason' => $order->driver_id ? 'no_response' : 'declined',
                'driver_name' => $order->driver
                    ? trim($order->driver->first_name.' '.$order->driver->last_name)
                    : null,
                'from_address' => $order->from_address,
                'to_address' => $order->to_address,
                'waiting_seconds' => $order->driver_waiting_seconds,
                'url' => route('order.edit', $order->id),
            ])->values()
        );
    }
}
