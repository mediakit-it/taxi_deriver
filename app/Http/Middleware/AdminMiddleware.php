<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();

            // Заблокированный сотрудник теряет доступ в панель на
            // следующем же запросе: сессия завершается сразу.
            if ($user->isBlocked()) {
                Auth::guard('web')->logout();

                return redirect('/');
            }

            if (in_array($user->role, User::PANEL_ROLES, true)) {
                return $next($request);
            }
        }

        return redirect('/');
    }
}
