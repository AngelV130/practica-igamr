<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class CheckRol
{
    /**
     * Handle an incoming request.
     *
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if(!in_array($request->user()->rol, $roles)){
            return response()->json([
                'message' => 'Unauthorized',
                'status' => 401
            ], 401);
        }
        return $next($request);
    }
}
