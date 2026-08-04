<?php

namespace App\Http\Middleware;

use App\Services\CashierHistoryService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TrackCashierHistory
{
    public function __construct(
        protected CashierHistoryService $cashierHistoryService
    ) {}
    /**
     * Handle an incoming request.
     *
     * Only maintains existing sessions for cashier staff.
     * - Closes expired sessions (idle > 30 min)
     * - Updates last_activity_at (throttled to once per 60s)
     *
     * Session creation ONLY happens in AuthController after successful login.
     * This middleware does NOT create sessions — it only maintains them.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        if (! in_array($user->role, ['cashier'], true)) {
            return $next($request);
        }

        $this->cashierHistoryService->closeExpiredSessions(30);

        $activeSession = $this->cashierHistoryService->getActiveSession($user);

        if ($activeSession) {
            $this->cashierHistoryService->updateActivity($activeSession);
        }
        // No else — session creation is AuthController's job

        return $next($request);
    }
}
