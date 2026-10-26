<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\VerifyEmailRequest;
use App\Services\EmailVerificationService;
use Illuminate\Http\JsonResponse;

class EmailVerificationController extends Controller
{
    public function __construct(private readonly EmailVerificationService $verification)
    {
    }

    /**
     * POST /api/v1/auth/verify-email {email, code}
     *
     * Проверка кода подтверждения почты, отправленного через
     * POST /api/auth/verify2. При успехе ставит серверную метку
     * «email подтверждён», которую затем требует регистрация.
     */
    public function verify(VerifyEmailRequest $request): JsonResponse
    {
        $result = $this->verification->verify(
            $request->validated('email'),
            $request->validated('code'),
        );

        if ($result === EmailVerificationService::RESULT_OK) {
            return response()->json(['status' => 'success']);
        }

        $message = match ($result) {
            EmailVerificationService::RESULT_EXHAUSTED => 'Превышено число попыток ввода кода. Запросите новый код.',
            EmailVerificationService::RESULT_EXPIRED => 'Код подтверждения истёк. Запросите новый код.',
            default => 'Неверный код подтверждения.',
        };

        return response()->json(['message' => $message], 422);
    }
}
