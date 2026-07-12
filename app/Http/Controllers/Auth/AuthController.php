<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\StaffSessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function showLogin(): \Inertia\Response
    {
        return Inertia::render('Auth/Login');
    }

    public function showCustomerLogin(): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('customer.menu');
    }

    public function customerLogin(Request $request): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('customer.menu');
    }

    public function login(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $key = Str::lower($request->email).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            return back()->withErrors([
                'email' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.",
            ]);
        }

        // Role validation BEFORE authentication attempt
        $existingUser = \App\Models\User::where('email', $request->email)->first();
        if ($existingUser) {
            if ($request->is('kasir/*') && !in_array($existingUser->role, ['cashier', 'admin'])) {
                return back()->withErrors([
                    'email' => 'Akun ini tidak memiliki akses ke Kasir.',
                ]);
            }
        }

        $guard = 'web';

        if (! Auth::guard($guard)->attempt($request->only('email', 'password'))) {
            RateLimiter::hit($key, 60);

            return back()->withErrors(['email' => 'Email atau kata sandi salah']);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        $user = Auth::guard($guard)->user();

        app(StaffSessionService::class)->startSession($user);

        if ($user->role === 'admin') {
            return redirect()->to('/admin');
        }

        return Inertia::location(route('kasir.pesanan-baru'));
    }

    public function logout(Request $request): \Illuminate\Http\RedirectResponse
    {
        $guard = 'web';
        $user = Auth::guard($guard)->user();

        if ($user && $user->role === 'cashier') {
            $activeSession = app(StaffSessionService::class)->getActiveSession($user);
            if ($activeSession) {
                app(StaffSessionService::class)->endSession($activeSession);
            }
        }

        Auth::guard($guard)->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('kasir.login');
    }
}
