<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckAdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::user()->role === 'admin') {
            return $next($request);
        }

        // If the user is not an admin, return a 403 Forbidden response
        return response()->json(['error' => 'Forbidden'], 403);
    }
}
