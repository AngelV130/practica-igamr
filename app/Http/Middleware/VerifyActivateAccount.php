<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class VerifyActivateAccount
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if(!Auth::check())
            return redirect()->route('login');
        if (Auth::user()->isActivated())
            return $next($request);
        else
            return redirect()->back()->withErrors([
                'message' => 'Cuenta no activa',
            ])->withInput();
    }
}
