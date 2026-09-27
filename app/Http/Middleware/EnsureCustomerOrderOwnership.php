<?php

namespace App\Http\Middleware;

use App\Models\Order;
use App\Support\CustomerSession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Reject any customer request that targets an order the current session does
 * not own.
 *
 * This is applied to every `/pelanggan/pesanan/{order}/*` and
 * `/api/pesanan/{order}/*` route (including the QRIS proof upload) so an
 * anonymous visitor cannot read or mutate another customer's order by walking
 * the sequential order id.
 *
 * The route model binding has already resolved `{order}` by the time this runs
 * (SubstituteBindings has a higher middleware priority), so a foreign id gives
 * a 403 and an unknown id keeps the normal 404.
 */
class EnsureCustomerOrderOwnership
{
    public function handle(Request $request, Closure $next): Response
    {
        $order = $request->route('order');

        if ($order instanceof Order && ! CustomerSession::owns($order)) {
            if ($request->expectsJson() && ! $request->header('X-Inertia')) {
                // Axios/API callers get JSON instead of an Inertia HTML page.
                return response()->json([
                    'message' => 'Anda tidak berhak mengakses pesanan ini.',
                ], 403);
            }

            abort(403);
        }

        return $next($request);
    }
}
