<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserNotBlocked
{
    /**
     * Заблокированному пользователю (флаг users.is_blocked, ставится
     * в админке) закрыт доступ по уже выданным API-токенам: токен сам
     * по себе валиден, поэтому проверка идёт на каждый запрос после
     * auth:api. Форма ошибки - как у остальных отказов API.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('api');

        if ($user && $user->is_blocked) {
            return response()->json([
                'status' => 'error',
                'message' => 'Пользователь заблокирован',
            ], 403);
        }

        return $next($request);
    }
}
