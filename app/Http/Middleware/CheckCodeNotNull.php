<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckCodeNotNull
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->code === null) {
            // El código del usuario es nulo, puedes realizar acciones adicionales si es necesario.
            // Por ejemplo, redirigir al usuario a una página específica.
            return redirect('/ver');
        }

        return $next($request);
    }
}
