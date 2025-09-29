<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class UserController extends Controller
{
    //
    public function showLoginForm()
    {
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
                $request->session()->put('user', $user);
                return redirect()->route('admin.listofPrice');
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

}
