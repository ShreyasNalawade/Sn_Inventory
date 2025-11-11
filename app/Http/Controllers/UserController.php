<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class UserController extends Controller
{
    //
    public function showLoginForm()
    {
        // If user is already authenticated, redirect to product list
        if (Auth::check()) {
            return redirect()->route('admin.listofPrice');
        }

        return view('admin.login');
    }

    public function showlistofPrice()
    {
        $oilProducts = Product::where('type_product', 'oil')
            ->get()
            ->groupBy('brand_name');

        $groceryProducts = Product::where('type_product', 'grocery')->get();
        $masalaProducts = Product::where('type_product', 'masala')->get();
        $otherProducts = Product::where('type_product', 'other')->get();

        return view('admin.listofPrice', compact('oilProducts', 'groceryProducts', 'masalaProducts', 'otherProducts'));
    }


    public function loginStore(Request $request)
    {

        // dd($request->all());
        // Step 1: Validate input
        $request->validate([
            'mobileNumber' => 'required|numeric|digits:10',
            'pin' => 'required|numeric|digits:4',
        ]);

        $mobileNumber = $request->input('mobileNumber');
        $pins = $request->input('pin');



        if ($pins == null) {
            return back()->withErrors([
                'pin' => 'PIN is required.',
            ])->withInput();
        }

        $user = User::where('phone_number', $mobileNumber)->first();

        if ($user) {
            if ($user->pin == $pins) {
                // $request->session()->put('user', $user);
                // $request->session()->put('last_login_time', now());
                // $request->session()->save();
                // return redirect()->route('admin.listofPrice');
                // 4. Log the user in using Laravel's Auth system
                Auth::login($user);

                // 5. Regenerate the session to prevent security issues
                $request->session()->regenerate();

                // 6. Redirect to the protected area
                return redirect()->intended(route('admin.listofPrice'));
            } else {
                return back()->withErrors([
                    'pin' => 'Incorrect PIN.',
                ])->withInput();
            }
        } else {
            return back()->withErrors([
                'mobileNumber' => 'User not found.',
            ])->withInput();
        }

    }

    public function logout(Request $request)
    {
        // Logout the user
        Auth::logout();

        // Invalidate the session
        $request->session()->invalidate();

        // Regenerate CSRF token
        $request->session()->regenerateToken();

        // Redirect to login page
        return redirect()->route('login.form')->with('success', 'You have been logged out successfully.');
    }

}
