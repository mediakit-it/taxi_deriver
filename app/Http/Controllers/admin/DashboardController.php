<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DriverLocation;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\View\View;

class DashboardController extends Controller
{
    // Статусы заказов, которые прямо сейчас в работе
    private const ACTIVE_STATUSES = [
        Order::STATUS_PENDING,
        Order::STATUS_SEARCHING,
        Order::STATUS_SEARCHING_OTHER,
        Order::STATUS_ASSIGNED,
        Order::STATUS_ON_PLACE,
        Order::STATUS_IN_PROGRESS,
    ];

    // Главная страница админки: живые счётчики за сегодня и последние заказы
    public function index(): View
    {
        $todayStart = now()->startOfDay();

        // Завершённые сегодня заказы: статус completed и обновлены сегодня
        $completedToday = Order::where('status', Order::STATUS_COMPLETED)
            ->where('updated_at', '>=', $todayStart);

        $stats = [
            'orders_today' => Order::where('created_at', '>=', $todayStart)->count(),
            'completed_today' => (clone $completedToday)->count(),
            'revenue_today' => (float) ((clone $completedToday)->sum('price') ?? 0),
            'drivers_online' => DriverLocation::where('updated_at', '>=', now()->subMinutes(5))->count(),
            'active_orders' => Order::whereIn('status', self::ACTIVE_STATUSES)->count(),
        ];

        // Заказы за сегодня по каждому статусу, включая нулевые
        $rawByStatus = Order::where('created_at', '>=', $todayStart)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $ordersByStatus = [];
        foreach (Order::statusLabels() as $status => $label) {
            $ordersByStatus[] = [
                'status' => $status,
                'label' => $label,
                'total' => (int) ($rawByStatus[$status] ?? 0),
            ];
        }

        $latestOrders = Order::orderByDesc('id')->limit(10)->get();

        $currency = Setting::get('currency_label') ?: 'руб.';

        return view('admin.index', compact('stats', 'ordersByStatus', 'latestOrders', 'currency'));
    }
}
