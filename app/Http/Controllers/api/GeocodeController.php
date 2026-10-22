<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\GeocodeRequest;
use App\Http\Requests\Api\ReverseGeocodeRequest;
use App\Services\YandexGeocodeService;
use Illuminate\Http\JsonResponse;

class GeocodeController extends Controller
{
    public function __construct(private readonly YandexGeocodeService $geocoder)
    {
    }

    public function geocode(GeocodeRequest $request): JsonResponse
    {
        return $this->respond($this->geocoder->geocode($request->validated('address')));
    }

    public function reverse(ReverseGeocodeRequest $request): JsonResponse
    {
        return $this->respond($this->geocoder->reverse(
            (float) $request->validated('lat'),
            (float) $request->validated('lng'),
        ));
    }

    private function respond(?array $point): JsonResponse
    {
        if (! $this->geocoder->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'Geocoder is not configured',
            ], 503);
        }

        if ($point === null) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found',
            ], 404);
        }

        return response()->json(['success' => true, 'data' => $point]);
    }
}
