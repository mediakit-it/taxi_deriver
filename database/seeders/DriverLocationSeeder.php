<?php

namespace Database\Seeders;

use App\Models\DriverLocation;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;

class DriverLocationSeeder extends Seeder
{
    /**
     * Геопозиции шести водителей: четверо свободных и двое занятых.
     *
     * Занятые берутся из заказов в работе (сначала in_progress, потом
     * назначенные): владелец позиции - именно водитель этого заказа.
     * Позиция у водителя одна (user_id уникален), поэтому заказы для
     * занятых позиций отбираются по одному на водителя.
     */
    public function run(): void
    {
        $busyOrders = Order::whereIn('status', [Order::STATUS_IN_PROGRESS, Order::STATUS_ASSIGNED])
            ->whereNotNull('driver_id')
            ->orderByRaw("CASE WHEN status = 'in_progress' THEN 0 ELSE 1 END")
            ->orderBy('id')
            ->get()
            ->unique('driver_id')
            ->take(2);

        $busyDriverIds = [];

        foreach ($busyOrders as $order) {
            DriverLocation::factory()->busy()->create([
                'user_id' => $order->driver_id,
                'order_id' => $order->id,
            ]);

            $busyDriverIds[] = $order->driver_id;
        }

        $freeDrivers = User::where('role', 'driving')
            ->whereNotIn('id', $busyDriverIds)
            ->orderBy('id')
            ->take(6 - count($busyDriverIds))
            ->get();

        foreach ($freeDrivers as $driver) {
            DriverLocation::factory()->create([
                'user_id' => $driver->id,
            ]);
        }
    }
}
