<?php

namespace App\Http\Middleware;

use App\Models\ActiveStaffSession;
use App\Models\DeviceSession;
use App\Models\WorkSession;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckWorkSession
{
    /**
     * Check if the currently active staff member has an active work session
     * for today's day-of-week and time range, and inject the result into
     * the request attributes for HandleInertiaRequests to share.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $workSession = null;

        $deviceUuid = $request->session()->get('device_session_id');

        if ($deviceUuid) {
            $deviceSession = DeviceSession::where('device_uuid', $deviceUuid)->first();

            if ($deviceSession) {
                $activeStaff = ActiveStaffSession::where('device_session_id', $deviceSession->id)
                    ->where('active_context', 'active')
                    ->active()
                    ->first();

                if ($activeStaff && $activeStaff->user_id) {
                    $now = Carbon::now();
                    $currentDayOfWeek = (int) $now->dayOfWeek;

                    $workSession = WorkSession::where('user_id', $activeStaff->user_id)
                        ->where('is_active', true)
                        ->whereJsonContains('day_of_week', $currentDayOfWeek)
                        ->whereTime('start_time', '<=', $now->format('H:i:s'))
                        ->whereTime('end_time', '>=', $now->format('H:i:s'))
                        ->first();
                }
            }
        }

        $request->attributes->set('work_session_check', $workSession ? [
            'id' => $workSession->id,
            'start_time' => $workSession->start_time->format('H:i:s'),
            'end_time' => $workSession->end_time->format('H:i:s'),
            'is_within_session' => true,
        ] : [
            'is_within_session' => false,
        ]);

        return $next($request);
    }
}
