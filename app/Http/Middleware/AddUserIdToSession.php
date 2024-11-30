<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
class AddUserIdToSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // إذا كان المستخدم مسجل دخول، قم بتخزين user_id في الجلسة
        if (Auth::check()) {
            session(['user_id' => Auth::id()]);
        }

        return $next($request);
    }
}
