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
        $locations = DriverLocation::with(['user.currentCar', 'order'])
            ->get(['user_id', 'lat', 'lng', 'is_zakaz', 'order_id']);

        return response()->json([
            'status' => true,
            'data' => $locations
        ]);
    }
}
