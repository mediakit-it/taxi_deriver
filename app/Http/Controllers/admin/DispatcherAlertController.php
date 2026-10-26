<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;

class DispatcherAlertController extends Controller
{
    /**
     * Заказы, чей назначенный водитель не ответил вовремя.
     * Источник правды для панели диспетчерских сигналов в админке.
     */
    public function overdue(): JsonResponse
    {
        $orders = Order::awaitingDriverResponse(Order::driverAcceptTimeout())
            ->with('driver')
            ->orderBy('driver_offered_at')
            ->get();

        return response()->json(
            $orders->map(fn (Order $order) => [
                'order_id' => $order->id,
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
