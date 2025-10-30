<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthCheck
{
    public function handle(Request $request, Closure $next)
    {
        // If user is not logged in
        if (!$request->session()->has('user')) {
            return redirect()->route('login.form')->with('error', 'Please login first.');
        }

        // If session exists, check expiration time (manually in case browser doesn't clear)
        $lastLogin = $request->session()->get('last_login_time');
        $now = now();

        // If more than 24 hours passed → logout
        if ($lastLogin && $now->diffInMinutes($lastLogin) > 1440) {
            $request->session()->forget(['user', 'last_login_time']);
            return redirect()->route('login.form')->with('error', 'Session expired, please login again.');
        }

        return $next($request);
    }
}
