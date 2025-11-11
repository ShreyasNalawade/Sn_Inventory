<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckSessionExpiry
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if a user was previously logged in (session had auth data)
        // but is no longer authenticated now. This indicates an expired session.
        if ($request->hasSession() && $request->session()->has('_token') && !Auth::check() && $request->session()->has(Auth::getName())) {
            // Forget the old auth session data and regenerate the token
            $request->session()->forget(Auth::getName());
            $request->session()->regenerateToken();
            return redirect()->route('login.form')->with('error', 'Session expired, please login again.');
        }

        return $next($request);
    }
}
