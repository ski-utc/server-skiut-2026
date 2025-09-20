<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if the user is authenticated and is an admin
        if (!Auth::check() || !Auth::user()->admin()) {
            return redirect()->route('home');
        }

        return $next($request);  // Allow access if authenticated and admin
    }
}
