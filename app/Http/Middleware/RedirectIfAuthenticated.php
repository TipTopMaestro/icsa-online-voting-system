<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();
                
                // Redirect based on user role
                $redirectUrl = match($user->role) {
                    'admin' => '/admin/dashboard',
                    'voter' => '/voter/dashboard',
                    'candidate' => '/candidate/dashboard',
                    default => '/dashboard',
                };
                
                return redirect($redirectUrl);
            }
        }

        return $next($request);
    }
}
