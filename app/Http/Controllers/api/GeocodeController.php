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
        $limit = (int) ($request->validated('limit') ?? 1);

        if ($limit > 1) {
            return $this->respondList(
                $this->geocoder->geocodeMany($request->validated('address'), $limit)
            );
        }

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

    /**
     * Multi-result mode: data is a list, and "nothing found" is a
     * successful empty list - the search UI shows no suggestions
     * instead of an error. Only a missing API key stays a 503.
     */
    private function respondList(array $points): JsonResponse
    {
        if (! $this->geocoder->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'Geocoder is not configured',
            ], 503);
        }

        return response()->json(['success' => true, 'data' => $points]);
    }
}
