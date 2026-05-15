<?php

namespace App\Http\Middleware;

use App\Services\StaffSessionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TrackStaffSession
{
    /**
     * Handle an incoming request.
     *
     * Only tracks sessions for cashier and kitchen staff.
     * - Closes expired sessions (idle > 30 min)
     * - Updates last_activity_at (throttled to once per 60s)
     * - Creates a new session if none exists
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        if (! in_array($user->role, ['cashier', 'kitchen'], true)) {
            return $next($request);
        }

        /** @var StaffSessionService $service */
        $service = app(StaffSessionService::class);

        $service->closeExpiredSessions(30);

        $activeSession = $service->getActiveSession($user);

        if ($activeSession) {
            $service->updateActivity($activeSession);
        } else {
            $service->startSession($user);
        }

        return $next($request);
    }
}
