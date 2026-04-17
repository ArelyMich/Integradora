<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Check2FASession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar si existe la sesión 2fa_user_id
        if (!session()->has('2fa_user_id')) {
            // Si no existe, redirigir al login
            return redirect('/')->withErrors(['email' => 'Debes iniciar sesión primero.']);
        }

        return $next($request);
    }
}
