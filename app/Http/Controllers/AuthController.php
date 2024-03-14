<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function login()
    {
        if (!Auth::user()) {
            return view('auth.login');
        }
        else {
            return redirect()->route('dashboard');
        }
    }

    public function checkAuth(Request $request)
    {
        if (Auth::attempt($request->only('email', 'password'))) {
            return redirect()->route('dashboard');
        }
        else {
            return redirect()->route('login')->withErrors([
                'email' => 'Credentials do not match',
            ]);
        }
    }

    public function logoutUser(Request $request)
    {
        Session::flush();
        Auth::logout();
        return redirect()->route('login');
    }
}
