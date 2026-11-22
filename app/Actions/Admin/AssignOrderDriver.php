<?php

namespace App\Actions\Admin;

use App\Events\OrderSent;
use App\Events\OrderStatusChanged;
use App\Exceptions\DriverAssignmentException;
use App\Models\DriverLocation;
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
 *
 * Защита назначения (пакет замечаний 22.11.2026): нового водителя
 * нельзя назначить, если он уже занят активным заказом или не на
 * линии - раньше звонок уходил занятому, а принятие падало с
 * ошибкой уже у водителя в приложении. Проверка живёт только
 * здесь, в единственной точке назначения: оба экрана админки
 * получают одинаковое поведение, отказ - DriverAssignmentException
 * с человекочитаемым текстом для диспетчера.
 */
class AssignOrderDriver
{
    public function __invoke(Order $order, ?int $driverId): Order
    {
        $oldDriver = $order->driver_id !== null ? (int) $order->driver_id : null;

        if ($driverId !== null && $driverId !== $oldDriver) {
            $this->guardDriverCanTakeOrder($order, $driverId);
        }

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

    /**
     * Назначить можно только свободного и находящегося на линии
     * водителя. «Занят» - есть другой активный заказ (назначен,
     * водитель на месте или поездка идёт). «На линии» - статус
     * работы по свежести геопозиции (DriverLocation::work_status):
     * нет строки геопозиции - водитель не на линии.
     */
    private function guardDriverCanTakeOrder(Order $order, int $driverId): void
    {
        $busyOrder = Order::query()
            ->where('driver_id', $driverId)
            ->whereIn('status', [
                Order::STATUS_ASSIGNED,
                Order::STATUS_ON_PLACE,
                Order::STATUS_IN_PROGRESS,
            ])
            ->whereKeyNot($order->id)
            ->first();

        if ($busyOrder) {
            throw new DriverAssignmentException("Водитель уже занят на заказе №{$busyOrder->id}.");
        }

        $location = DriverLocation::query()
            ->where('user_id', $driverId)
            ->first();

        if (! $location || $location->work_status !== DriverLocation::STATUS_ONLINE) {
            throw new DriverAssignmentException('Водитель не на линии.');
        }
    }
}
