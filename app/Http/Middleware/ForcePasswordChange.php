<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->must_change_password) {
            if ($request->routeIs('user.password') ||
                $request->routeIs('user.password.update') ||
                $request->routeIs('logout')) {
                return $next($request);
            }

            return redirect()->route('user.password')
                ->with('info', 'Você precisa alterar sua senha antes de continuar.');
        }

        return $next($request);
    }
}
