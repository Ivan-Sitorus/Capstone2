<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActiveStaffSession;
use App\Models\DeviceSession;
use App\Models\User;
use App\Models\WorkSession;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class DeviceAuthController extends Controller
{
    /**
     * Login staff via device session — creates an ActiveStaffSession.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Rate limiting per email+ip
        $key = Str::lower($validated['email']).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'message' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.",
            ], 429);
        }

        $user = User::where('email', $validated['email'])
            ->whereIn('role', ['cashier', 'admin'])
            ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            RateLimiter::hit($key, 60);

            return response()->json([
                'message' => 'Email atau kata sandi salah.',
            ], 401);
        }

        RateLimiter::clear($key);

        // Resolve device session from UUID stored in Laravel session
        $deviceUuid = $request->session()->get('device_session_id');
        if (! $deviceUuid) {
            return response()->json([
                'message' => 'Device session not initialized.',
            ], 400);
        }

        $deviceSession = DeviceSession::where('device_uuid', $deviceUuid)->first();
        if (! $deviceSession) {
            return response()->json([
                'message' => 'Device session not found.',
            ], 400);
        }

        // Deactivate any currently-active session on this device
        ActiveStaffSession::where('device_session_id', $deviceSession->id)
            ->where('active_context', 'active')
            ->update(['active_context' => null]);

        $staffSession = ActiveStaffSession::create([
            'device_session_id' => $deviceSession->id,
            'user_id' => $user->id,
            'pin_verified_at' => now(),
            'active_context' => 'active',
        ]);

        return response()->json([
            'staff_session_id' => $staffSession->id,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }

    /**
     * Logout a single staff session by its ID.
     */
    public function logout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'staff_session_id' => 'required|integer|exists:active_staff_sessions,id',
        ]);

        ActiveStaffSession::where('id', $validated['staff_session_id'])->delete();

        return response()->json(['message' => 'Staff session deleted.']);
    }

    /**
     * Logout ALL staff sessions on the current device.
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $deviceUuid = $request->session()->get('device_session_id');
        if (! $deviceUuid) {
            return response()->json(['message' => 'Device session not initialized.'], 400);
        }

        $deviceSession = DeviceSession::where('device_uuid', $deviceUuid)->first();
        if (! $deviceSession) {
            return response()->json(['message' => 'Device session not found.'], 400);
        }

        ActiveStaffSession::where('device_session_id', $deviceSession->id)->delete();

        return response()->json(['message' => 'All staff sessions on this device deleted.']);
    }

    /**
     * Switch the active staff context to a different staff session.
     */
    public function switchStaff(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'staff_session_id' => 'required|integer|exists:active_staff_sessions,id',
        ]);

        $staffSession = ActiveStaffSession::with('user')->findOrFail($validated['staff_session_id']);

        // Deactivate all other sessions on this device
        ActiveStaffSession::where('device_session_id', $staffSession->device_session_id)
            ->where('active_context', 'active')
            ->update(['active_context' => null]);

        // Activate the selected one
        $staffSession->update(['active_context' => 'active']);

        return response()->json([
            'message' => 'Switched active staff.',
            'staff_session_id' => $staffSession->id,
            'user' => [
                'id' => $staffSession->user->id,
                'name' => $staffSession->user->name,
                'email' => $staffSession->user->email,
                'role' => $staffSession->user->role,
            ],
        ]);
    }

    /**
     * Return list of active staff sessions for this device.
     */
    public function activeStaff(Request $request): JsonResponse
    {
        $deviceUuid = $request->session()->get('device_session_id');
        if (! $deviceUuid) {
            return response()->json(['message' => 'Device session not initialized.'], 400);
        }

        $deviceSession = DeviceSession::where('device_uuid', $deviceUuid)->first();
        if (! $deviceSession) {
            return response()->json([], 200);
        }

        $sessions = ActiveStaffSession::with('user')
            ->where('device_session_id', $deviceSession->id)
            ->active() // whereNotNull('pin_verified_at')
            ->get()
            ->map(fn (ActiveStaffSession $s) => [
                'staff_session_id' => $s->id,
                'active_context' => $s->active_context,
                'user' => $s->user ? [
                    'id' => $s->user->id,
                    'name' => $s->user->name,
                    'email' => $s->user->email,
                    'role' => $s->user->role,
                ] : null,
            ]);

        return response()->json(['sessions' => $sessions]);
    }

    /**
     * Extend the current work session by 30 minutes.
     */
    public function extendSession(Request $request): JsonResponse
    {
        $workSession = $this->resolveActiveWorkSession($request);

        if (! $workSession) {
            return response()->json(['message' => 'Tidak ada sesi kerja aktif.'], 404);
        }

        $now = Carbon::now();
        $currentEnd = Carbon::parse($workSession->end_time);
        $newEnd = $now->copy()->addMinutes(30);

        if ($newEnd->gt($currentEnd)) {
            $workSession->update(['end_time' => $newEnd->format('H:i:s')]);
        }

        return response()->json([
            'message' => 'Sesi kerja diperpanjang 30 menit.',
            'end_time' => $newEnd->format('H:i:s'),
        ]);
    }

    /**
     * Allow the current staff to continue working without an active work session.
     */
    public function continueWithoutSession(Request $request): JsonResponse
    {
        $request->session()->forget('work_session_check');

        return response()->json([
            'message' => 'Melanjutkan tanpa sesi kerja.',
            'is_within_session' => false,
        ]);
    }

    /**
     * Resolve the active WorkSession for the current staff on this device.
     */
    private function resolveActiveWorkSession(Request $request): ?WorkSession
    {
        $deviceUuid = $request->session()->get('device_session_id');
        if (! $deviceUuid) {
            return null;
        }

        $deviceSession = DeviceSession::where('device_uuid', $deviceUuid)->first();
        if (! $deviceSession) {
            return null;
        }

        $activeStaff = ActiveStaffSession::where('device_session_id', $deviceSession->id)
            ->where('active_context', 'active')
            ->active()
            ->first();

        if (! $activeStaff || ! $activeStaff->user_id) {
            return null;
        }

        $now = Carbon::now();
        $currentDayOfWeek = (int) $now->dayOfWeek;

        return WorkSession::where('user_id', $activeStaff->user_id)
            ->where('is_active', true)
            ->whereJsonContains('day_of_week', $currentDayOfWeek)
            ->whereTime('start_time', '<=', $now->format('H:i:s'))
            ->whereTime('end_time', '>=', $now->format('H:i:s'))
            ->first();
    }
}
