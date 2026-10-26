<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CarBindingCarsRequest;
use App\Services\CarBindingService;
use Illuminate\Http\JsonResponse;

class CarBindingController extends Controller
{
    public function __construct(private readonly CarBindingService $carBinding)
    {
    }

    // Список автомобилей для привязки устройства при установке
    // водительского приложения. Экран входа, авторизации ещё нет -
    // доступ только по паролю привязки из настроек; роут закрыт
    // троттлингом от перебора пароля.
    public function cars(CarBindingCarsRequest $request): JsonResponse
    {
        $result = $this->carBinding->carsForBinding($request->validated('password'));

        return match ($result['status']) {
            CarBindingService::STATUS_NOT_CONFIGURED => response()->json([
                'success' => false,
                'message' => 'Привязка не настроена',
            ], 403),
            CarBindingService::STATUS_WRONG_PASSWORD => response()->json([
                'success' => false,
                'message' => 'Неверный пароль привязки',
            ], 403),
            default => response()->json([
                'success' => true,
                'data' => $result['cars'],
            ]),
        };
    }
}
