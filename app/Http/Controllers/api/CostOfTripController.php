<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CostOfTrip;
use Illuminate\Http\Request;

class CostOfTripController extends Controller
{
    // Получить все цены
    public function index()
    {
        $prices = CostOfTrip::all();
        
        return response()->json([
            'success' => true,
            'data' => $prices
        ]);
    }
    
    // Получить конкретную цену по name (City или Outside_the_City)
    public function getByName($name)
    {
        $price = CostOfTrip::where('name', $name)->first();
        
        if (!$price) {
            return response()->json([
                'success' => false,
                'message' => 'Цена не найдена'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $price
        ]);
    }
    
    // Получить цены в простом формате (key => value)
    public function getSimple()
    {
        $prices = CostOfTrip::all()->pluck('price', 'name')
            ->map(fn ($price) => $price === null ? null : CostOfTrip::formatPrice($price));
        
        return response()->json([
            'success' => true,
            'data' => [
                'city' => $prices['City'] ?? null,
                'outside_city' => $prices['Outside_the_City'] ?? null
            ]
        ]);
    }
}