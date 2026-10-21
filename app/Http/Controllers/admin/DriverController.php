<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DriverLocation;
use App\Models\User;
use Illuminate\View\View;

class DriverController extends Controller
{
    // Порядок статусов в списке: сначала те, кто на линии
    private const STATUS_ORDER = [
        DriverLocation::STATUS_ONLINE => 0,
        DriverLocation::STATUS_OFFLINE => 1,
        DriverLocation::STATUS_NOT_WORKING => 2,
    ];

    // Водители со статусами работы (на линии / не на линии / не работает)
    public function index(): View
    {
        $drivers = User::where('role', 'driving')
            ->with(['car', 'location'])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get()
            ->sortBy(fn (User $driver) => self::STATUS_ORDER[$driver->location?->work_status ?? DriverLocation::STATUS_NOT_WORKING])
            ->values();

        return view('admin.drivers.index', compact('drivers'));
    }
}
