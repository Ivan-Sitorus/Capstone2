<?php

namespace App\Http\Middleware;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user(); // resolved once, reused below

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    'id'   => $user->id,
                    'name' => $user->name,
                    'role' => $user->role,
                ] : null,
            ],
            'flash' => [
                'success' => fn() => session('success'),
                'error'   => fn() => session('error'),
            ],
            // Lazy closure — only resolves when Inertia actually needs it
            'pendingOrderCount' => fn() => $user && in_array($user->role, ['cashier', 'admin'])
                ? Cache::remember('pending_order_count', 30, fn() => Order::where('status', Order::STATUS_PENDING)
                    ->where(function ($q) {
                        $q->where('payment_method', 'cash')
                          ->orWhere(function ($q2) {
                              $q2->where('payment_method', 'qris')
                                 ->whereNotNull('payment_proof');
                          });
                    })
                    ->count())
                : 0,
        ]);
    }
}
