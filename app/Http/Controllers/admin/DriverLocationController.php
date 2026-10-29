<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DriverLocation;

class DriverLocationController extends Controller
{

    public function index(){

        return view('admin.locationUser.index');
    }

    public function getAllDriverLocations()
    {
        // updated_at нужен в выборке: без него work_status у каждой
        // строки считается от null и всегда выходит «Не работает»,
        // хотя статус - часть данных карты.
        $locations = DriverLocation::with(['user.currentCar', 'order'])
            ->get(['user_id', 'lat', 'lng', 'is_zakaz', 'order_id', 'updated_at']);

        $locations->each->append(['work_status', 'work_status_label']);

        return response()->json([
            'status' => true,
            'data' => $locations
        ]);
    }
}
