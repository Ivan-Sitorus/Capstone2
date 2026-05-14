<?php

namespace App\Http\Middleware;

use App\Models\DeviceSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class InitializeDeviceSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $deviceUuid = $request->session()->get('device_session_id');

        if (! $deviceUuid) {
            $deviceUuid = (string) Str::uuid();

            DeviceSession::create([
                'device_uuid' => $deviceUuid,
                'device_name' => $request->userAgent(),
                'last_seen_at' => now(),
            ]);

            $request->session()->put('device_session_id', $deviceUuid);
        } else {
            DeviceSession::where('device_uuid', $deviceUuid)
                ->update(['last_seen_at' => now()]);
        }

        return $next($request);
    }
}
