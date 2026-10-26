<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Доступ только перечисленным ролям панели, например:
     * ->middleware('role:admin,superadmin'). Используется поверх
     * middleware 'admin', который отсекает все непанельные роли.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = Auth::guard('web')->user();

        if ($user && in_array($user->role, $roles, true)) {
            return $next($request);
        }

        abort(403);
    }
}
