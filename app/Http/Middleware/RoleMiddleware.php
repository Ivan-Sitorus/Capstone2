<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        if (! Auth::check()) {
            return redirect()->route('kasir.login');
        }

        if (! in_array(Auth::user()->role->value, $roles)) {
            if ($request->is('admin') || $request->is('admin/*')) {
                return redirect()->to('/admin/login');
            }

            return redirect()->route('kasir.login');
        }

        return $next($request);
    }
}
