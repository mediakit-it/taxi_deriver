<?php

namespace App\Actions\Admin;

use App\Events\OrderSent;
use App\Events\OrderStatusChanged;
use App\Models\Order;

/**
 * Назначение водителя на заказ диспетчером - единственная точка
 * этой операции. Её используют форма редактирования заказа
 * (OrderController::update) и быстрое назначение прямо из списка
 * заказов (OrderController::assignDriver): логика предложения
 * заказа водителю не должна расходиться между экранами.
 *
 * Поведение (как исторически делал update): при смене водителя
 * метка предложения driver_offered_at начинается заново, при
 * снятии - сбрасывается; заказ с новым водителем уходит в статус
 * searching и получает персональное предложение (OrderSent);
 * заказ без водителя анонсируется всем водителям (OrderSent по роли).
 */
class AssignOrderDriver
{
    public function __invoke(Order $order, ?int $driverId): Order
    {
        $oldDriver = $order->driver_id !== null ? (int) $order->driver_id : null;

        if ($oldDriver !== $driverId) {
            $order->update(['driver_id' => $driverId]);

            // Момент предложения заказа водителю (для сторожа ответов
            // водителей): смена - отсчёт заново, снятие - сброс.
            $order->update(['driver_offered_at' => $driverId ? now() : null]);
        }

        if ($driverId && $driverId !== $oldDriver) {
            $order->update(['status' => Order::STATUS_SEARCHING]);
            event(new OrderSent($order, $driverId));
            event(new OrderStatusChanged($order, 'reassigned_by_dispatcher'));
        } elseif (! $driverId) {
            event(new OrderSent($order, role: 'driving'));
        }

        return $order;
    }
}
