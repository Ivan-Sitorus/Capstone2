<?php

namespace Database\Seeders;

use App\Models\StaffSession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class StaffSessionSeeder extends Seeder
{
    public function run(): void
    {
        $cashiers = User::where('role', 'cashier')->get();

        if ($cashiers->isEmpty()) {
            $this->command->warn('Tidak ada akun kasir. Jalankan UserSeeder terlebih dahulu.');
            return;
        }

        $now = now();
        $sessions = [];
        $sessionId = 1;

        foreach ($cashiers as $cashier) {
            // 6-10 sessions per cashier in the past 7 days
            $numSessions = rand(6, 10);

            for ($i = 0; $i < $numSessions; $i++) {
                $daysAgo = rand(0, 6);
                $loginHour = rand(7, 10);
                $loginMinute = rand(0, 59);
                $durationMinutes = rand(120, 540); // 2-9 hours

                $startedAt = Carbon::now()
                    ->subDays($daysAgo)
                    ->setHour($loginHour)
                    ->setMinute($loginMinute)
                    ->setSecond(0);

                $endedAt = $startedAt->copy()->addMinutes($durationMinutes);

                $sessions[] = [
                    'user_id' => $cashier->id,
                    'type' => 'cashier',
                    'session_id' => 'seed-' . ($sessionId++),
                    'started_at' => $startedAt,
                    'ended_at' => $endedAt,
                    'last_activity_at' => $endedAt,
                    'is_active' => false,
                    'created_at' => $startedAt,
                    'updated_at' => $endedAt,
                ];
            }
        }

        // Also add one currently active session for first cashier
        $firstCashier = $cashiers->first();
        $activeNow = $now->copy()->subHours(rand(1, 3));
        $sessions[] = [
            'user_id' => $firstCashier->id,
            'type' => 'cashier',
            'session_id' => 'seed-' . ($sessionId),
            'started_at' => $activeNow,
            'ended_at' => null,
            'last_activity_at' => $now,
            'is_active' => true,
            'created_at' => $activeNow,
            'updated_at' => $now,
        ];

        StaffSession::insert($sessions);

        $this->command->info(
            count($sessions) . ' sesi login kasir berhasil di-seed untuk ' .
            $cashiers->count() . ' akun kasir.'
        );
    }
}
