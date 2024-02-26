<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OnlyNamedRoute
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $routeName)
    {
        // Verificar si el nombre de la ruta coincide
        if ($request->route()->getName() !== $routeName) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
