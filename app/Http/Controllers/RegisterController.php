<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        if (User::count() > 0) {
            return redirect()->route('login');
        }

        return view('auth.register');
    }

    public function register(Request $request)
    {
        // Validate the form data
        $request->validate([
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
        ]);

        // Create a new user
        User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Redirect to the login page or any other desired page
        return redirect()->route('login');
    }
}
