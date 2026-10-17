<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Car;
use App\Models\User;

class CarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cars = Car::all();

        return view('admin.car.index',compact('cars'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::where('role','driving')->get();
        return view('admin.car.add',compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Car::where('user_id', $request->user_id)->update(['user_id' => null]);
        $car = Car::create([
            'user_id' => $request->user_id,
            'brand' => $request->brand,
            'model' => $request->model,
            'number_plate' => $request->number_plate,
        ]);

        if($car){
            return redirect(route('car.index'));
        }else{
            return back();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $car = Car::find($id);
        $users = User::where('role','driving')->get();

        return view('admin.car.edit',compact('car','users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        Car::where('user_id', $request->user_id)->update(['user_id' => null]);

        $car = Car::find($id)->update([
            'user_id' => $request->user_id,
            'brand' => $request->brand,
            'model' => $request->model,
            'number_plate' => $request->number_plate,
        ]);

        if($car){
            return redirect(route('car.index'));
        }else{
            return back();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $car = Car::find($id);
        if($car->delete()){
            return 'true';
        }
        else return 'false';
    }
}
