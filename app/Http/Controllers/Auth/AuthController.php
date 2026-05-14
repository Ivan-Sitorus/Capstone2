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
        if (Auth::check()) {
            $user = Auth::user();
            return match ($user->role) {
                'admin' => redirect()->to('/admin'),
                'kitchen' => redirect()->route('dapur.beranda'),
                default => Inertia::location(route('kasir.pesanan-baru')),
            };
        }

        return Inertia::render('Auth/Login');
    }

    public function showKitchenLogin()
    {
        return Inertia::render('Dapur/Login');
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

        $user = Auth::user();

        if ($user->role === 'admin') {
            return redirect()->to('/admin');
        }

        if ($user->role === 'kitchen') {
            return Inertia::location(route('dapur.beranda'));
        }

        if ($request->is('dapur/*')) {
            return Inertia::location(route('dapur.beranda'));
        }

        return Inertia::location(route('kasir.pesanan-baru'));
    }

    public function logout(Request $request)
    {
        $role = Auth::user()->role ?? 'cashier';

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($role === 'kitchen') {
            return redirect()->route('dapur.login');
        }

        return redirect()->route('kasir.login');
    }
}
