<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CostOfTrip;
use Illuminate\Http\Request;

class CostOfTripController extends Controller
{
    // Показать форму редактирования
    public function edit()
    {
        $city = CostOfTrip::where('name', 'City')->first();
        $outsideCity = CostOfTrip::where('name', 'Outside_the_City')->first();
        
        return view('admin.cost-of-trip.edit', compact('city', 'outsideCity'));
    }

    // Обновить цены
    public function update(Request $request)
    {
        $request->validate([
            'price_city' => 'required|numeric|min:0',
            'price_outside' => 'required|numeric|min:0',
            'display_name_city' => 'nullable|string|max:255',
            'display_name_outside' => 'nullable|string|max:255',
        ]);

        // Обновляем City
        CostOfTrip::where('name', 'City')->update([
            'price' => $request->price_city,
            'display_name' => $request->display_name_city,
        ]);

        // Обновляем Outside_the_City
        CostOfTrip::where('name', 'Outside_the_City')->update([
            'price' => $request->price_outside,
            'display_name' => $request->display_name_outside,
        ]);

        return redirect()->route('cost-of-trip.edit')
            ->with('success', 'Цены успешно обновлены');
    }
}