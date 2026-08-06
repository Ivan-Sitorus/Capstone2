<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\CashierHistory;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CashierHistoryService
{
    /**
     * Start a new session for the given user.
     *
     * Closes any existing active sessions on the SAME device (session_id)
     * before creating a new one. Sessions on OTHER devices remain active.
     * Only cashier users get sessions (admin/customer are skipped).
     */
    public function startSession(User $user): ?CashierHistory
    {
        if ($user->role !== UserRole::Cashier) {
            return null;
        }

        $this->closeAllSessionsForUser($user);

        return CashierHistory::create([
            'user_id' => $user->id,
            'session_id' => session()->getId(),
            'started_at' => now(),
            'last_activity_at' => now(),
            'is_active' => true,
        ]);
    }

    public function endSession(CashierHistory $session): void
    {
        $session->ended_at = now();
        $session->is_active = false;
        $session->save();
    }

    public function closeExpiredSessions(int $idleMinutes = 30): int
    {
        $threshold = now()->subMinutes($idleMinutes);

        return CashierHistory::where('is_active', true)
            ->where('last_activity_at', '<', $threshold)
            ->update([
                'ended_at' => DB::raw('last_activity_at'),
                'is_active' => false,
            ]);
    }

    public function getActiveSession(User $user): ?CashierHistory
    {
        return CashierHistory::where('user_id', $user->id)
            ->where('is_active', true)
            ->first();
    }

    public function getOrderCount(CashierHistory $session): int
    {
        $endTime = $session->ended_at ?? now();

        return Order::where('cashier_id', $session->user_id)
            ->whereBetween('created_at', [$session->started_at, $endTime])
            ->count();
    }

    public function updateActivity(CashierHistory $session): void
    {
        $lastActivity = Carbon::parse($session->last_activity_at);

        if ($lastActivity->diffInSeconds(now()) < 60) {
            return;
        }

        $session->last_activity_at = now();
        $session->save();
    }

    /**
     * Close all active sessions for the user on the CURRENT device only.
     *
     * Uses the Laravel session ID to isolate sessions per device/browser.
     * Active sessions on other devices remain untouched.
     */
    private function closeAllSessionsForUser(User $user): void
    {
        CashierHistory::where('user_id', $user->id)
            ->where('is_active', true)
            ->where('session_id', session()->getId())
            ->update([
                'ended_at' => now(),
                'is_active' => false,
            ]);
    }
}
