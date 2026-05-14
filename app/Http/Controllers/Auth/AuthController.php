<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function showLogin()
    {
        return Inertia::render('Auth/Login');
    }

    public function showKitchenLogin()
    {
        return Inertia::render('Kitchen/Login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $key = Str::lower($request->email) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors([
                'email' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.",
            ]);
        }

        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            RateLimiter::hit($key, 60);
            return back()->withErrors(['email' => 'Email atau kata sandi salah']);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        if ($request->is('kitchen/*')) {
            return Inertia::location(route('kitchen.index'));
        }

        return Inertia::location(route('cashier.pesanan-baru'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('cashier.login');
    }
}
