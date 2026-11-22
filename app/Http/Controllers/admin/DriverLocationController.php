<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DriverLocation;
use App\Models\DriverStatusPeriod;

class DriverLocationController extends Controller
{

    public function index(){

        return view('admin.locationUser.index');
    }

    public function getAllDriverLocations()
    {
        // updated_at нужен в выборке: без него work_status у каждой
        // строки считается от null и всегда выходит «Не работает»,
        // хотя статус - часть данных карты. is_online - по той же
        // причине (пакет №162): без флага статус считается только
        // по свежести, и ушедший с линии водитель выглядит на линии.
        $locations = DriverLocation::with(['user.currentCar', 'order'])
            ->get(['user_id', 'lat', 'lng', 'is_zakaz', 'is_online', 'order_id', 'updated_at']);

        $locations->each->append(['work_status', 'work_status_label']);

        // Начало текущего периода работы каждого водителя - одним
        // запросом на всю выдачу: карта показывает, как давно
        // водитель находится в этом статусе.
        $periods = DriverStatusPeriod::open()
            ->whereIn('user_id', $locations->pluck('user_id'))
            ->get()
            ->keyBy('user_id');

        $locations->each(function (DriverLocation $location) use ($periods) {
            $location->setAttribute(
                'status_since',
                $periods->get($location->user_id)?->started_at?->toIso8601String()
            );
        });

        return response()->json([
            'status' => true,
            'data' => $locations
        ]);
    }
}
