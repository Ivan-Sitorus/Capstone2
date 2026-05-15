<?php

namespace App\Services;

use App\Models\CashierSession;
use App\Models\KitchenSession;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;

class StaffSessionService
{
    /**
     * Start a new session for the given user.
     *
     * Closes any existing active sessions on the SAME device (session_id)
     * before creating a new one. Sessions on OTHER devices remain active.
     */
    public function startSession(User $user): CashierSession|KitchenSession|null
    {
        if ($user->role === 'cashier') {
            $this->closeAllSessionsForUser($user);
            return CashierSession::create([
                'user_id' => $user->id,
                'session_id' => session()->getId(),
                'started_at' => now(),
                'last_activity_at' => now(),
                'is_active' => true,
            ]);
        }

        if ($user->role === 'kitchen') {
            $this->closeAllSessionsForUser($user);
            return KitchenSession::create([
                'user_id' => $user->id,
                'session_id' => session()->getId(),
                'started_at' => now(),
                'last_activity_at' => now(),
                'is_active' => true,
            ]);
        }

        return null;
    }

    public function endSession(CashierSession|KitchenSession $session): void
    {
        $session->ended_at = now();
        $session->is_active = false;
        $session->save();
    }

    public function closeExpiredSessions(int $idleMinutes = 30): int
    {
        $threshold = now()->subMinutes($idleMinutes);
        $closedCount = 0;

        $expiredCashierSessions = CashierSession::where('is_active', true)
            ->where('last_activity_at', '<', $threshold)
            ->get();

        foreach ($expiredCashierSessions as $session) {
            $session->ended_at = $session->last_activity_at;
            $session->is_active = false;
            $session->save();
            $closedCount++;
        }

        $expiredKitchenSessions = KitchenSession::where('is_active', true)
            ->where('last_activity_at', '<', $threshold)
            ->get();

        foreach ($expiredKitchenSessions as $session) {
            $session->ended_at = $session->last_activity_at;
            $session->is_active = false;
            $session->save();
            $closedCount++;
        }

        return $closedCount;
    }

    public function getActiveSession(User $user): CashierSession|KitchenSession|null
    {
        return match ($user->role) {
            'cashier' => CashierSession::where('user_id', $user->id)
                ->where('is_active', true)
                ->first(),
            'kitchen' => KitchenSession::where('user_id', $user->id)
                ->where('is_active', true)
                ->first(),
            default => null,
        };
    }

    public function getOrderCount(CashierSession|KitchenSession $session): int
    {
        $endTime = $session->ended_at ?? now();

        if ($session instanceof CashierSession) {
            return Order::where('cashier_id', $session->user_id)
                ->whereBetween('created_at', [$session->started_at, $endTime])
                ->count();
        }

        return Order::where('processed_by', $session->user_id)
            ->where('status', Order::STATUS_SELESAI)
            ->whereBetween('created_at', [$session->started_at, $endTime])
            ->count();
    }

    public function updateActivity(CashierSession|KitchenSession $session): void
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
        $sessionId = session()->getId();

        CashierSession::where('user_id', $user->id)
            ->where('is_active', true)
            ->where('session_id', $sessionId)
            ->update([
                'ended_at' => now(),
                'is_active' => false,
            ]);

        KitchenSession::where('user_id', $user->id)
            ->where('is_active', true)
            ->where('session_id', $sessionId)
            ->update([
                'ended_at' => now(),
                'is_active' => false,
            ]);
    }
}
