<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AuthCheck
{
    public function handle(Request $request, Closure $next)
    {
        $isLoggedIn = $request->session()->has('user');
        $lastLogin = $request->session()->get('last_login_time');

        // If session expired (more than 24 hours old)
        if ($isLoggedIn && $lastLogin) {
            $now = Carbon::now();
            if ($now->diffInMinutes(Carbon::parse($lastLogin)) > 1440) {
                $request->session()->forget(['user', 'last_login_time']);
                return redirect()->route('login.form')->with('error', 'Session expired, please login again.');
            }
        }

        // ✅ If already logged in and trying to access login page — redirect to list
        if ($isLoggedIn && $request->is('login')) {
            return redirect()->route('admin.listofPrice');
        }

        // ✅ If not logged in and trying to access protected pages
        if (!$isLoggedIn && !$request->is('login') && !$request->is('login/store')) {
            return redirect()->route('login.form')->with('error', 'Please login first.');
        }

        return $next($request);
    }
}
