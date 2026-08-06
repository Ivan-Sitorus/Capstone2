<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Services\CashierHistoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function __construct(
        protected CashierHistoryService $cashierHistoryService
    ) {}
    public function showLogin(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function showCustomerLogin(): RedirectResponse
    {
        return redirect()->route('customer.menu');
    }

    public function customerLogin(Request $request): RedirectResponse
    {
        return redirect()->route('customer.menu');
    }

    public function login(Request $request): RedirectResponse
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
            if ($request->is('kasir/*') && $existingUser->role !== UserRole::Cashier) {
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

        $this->cashierHistoryService->startSession($user);

        if ($user->role === UserRole::Admin) {
            return redirect()->to('/admin');
        }

        return Inertia::location(route('kasir.pesanan-baru'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $guard = 'web';
        $user = Auth::guard($guard)->user();

        if ($user && $user->role === UserRole::Cashier) {
            $activeSession = $this->cashierHistoryService->getActiveSession($user);
            if ($activeSession) {
                $this->cashierHistoryService->endSession($activeSession);
            }
        }

        Auth::guard($guard)->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('kasir.login');
    }
}
