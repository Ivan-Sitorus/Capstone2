<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorkSession;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_work_session_for_mon_to_fri(): void
    {
        $user = User::factory()->create(['role' => 'cashier']);

        $session = WorkSession::create([
            'user_id' => $user->id,
            'day_of_week' => [1, 2, 3, 4, 5], // Monday to Friday
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('work_sessions', [
            'id' => $session->id,
            'user_id' => $user->id,
            'is_active' => true,
        ]);

        $this->assertEquals([1, 2, 3, 4, 5], $session->day_of_week);
        $this->assertEquals('08:00:00', $session->start_time->format('H:i:s'));
        $this->assertEquals('16:00:00', $session->end_time->format('H:i:s'));
    }

    public function test_is_currently_active_returns_true_when_in_schedule(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-13 10:00:00')); // Wednesday

        $user = User::factory()->create(['role' => 'cashier']);

        $session = WorkSession::create([
            'user_id' => $user->id,
            'day_of_week' => [1, 2, 3, 4, 5], // Mon-Fri includes Wednesday (3)
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
            'is_active' => true,
        ]);

        $this->assertTrue($session->isCurrentlyActive());

        Carbon::setTestNow();
    }

    public function test_is_currently_active_returns_false_outside_schedule(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-10 10:00:00')); // Sunday (0)

        $user = User::factory()->create(['role' => 'cashier']);

        $session = WorkSession::create([
            'user_id' => $user->id,
            'day_of_week' => [1, 2, 3, 4, 5], // Mon-Fri only
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
            'is_active' => true,
        ]);

        $this->assertFalse($session->isCurrentlyActive());

        Carbon::setTestNow();
    }

    public function test_is_currently_active_returns_false_outside_time_range(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-13 17:00:00')); // Wednesday after hours

        $user = User::factory()->create(['role' => 'cashier']);

        $session = WorkSession::create([
            'user_id' => $user->id,
            'day_of_week' => [1, 2, 3, 4, 5],
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
            'is_active' => true,
        ]);

        $this->assertFalse($session->isCurrentlyActive());

        Carbon::setTestNow();
    }

    public function test_is_currently_active_returns_false_when_not_active(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-13 10:00:00')); // Wednesday in range

        $user = User::factory()->create(['role' => 'cashier']);

        $session = WorkSession::create([
            'user_id' => $user->id,
            'day_of_week' => [1, 2, 3, 4, 5],
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
            'is_active' => false,
        ]);

        $this->assertFalse($session->isCurrentlyActive());

        Carbon::setTestNow();
    }
}
