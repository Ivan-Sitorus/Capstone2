<?php

namespace Tests\Feature;

use App\Http\Middleware\TrackCashierHistory;
use App\Models\Order;
use App\Models\CashierHistory;
use App\Models\User;
use App\Services\CashierHistoryService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class CashierHistoryTest extends TestCase
{
    use RefreshDatabase;

    private CashierHistoryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CashierHistoryService();
    }

    /** @test */
    public function test_start_cashier_session_creates_record(): void
    {
        $user = User::factory()->create(['role' => 'cashier']);

        $session = $this->service->startSession($user);

        $this->assertInstanceOf(CashierHistory::class, $session);
        $this->assertNotNull($session->id);
        $this->assertEquals($user->id, $session->user_id);
        $this->assertTrue($session->is_active);
        $this->assertNotNull($session->started_at);
        $this->assertNotNull($session->last_activity_at);
        $this->assertNull($session->ended_at);

        $this->assertDatabaseHas('cashier_histories', [
            'user_id' => $user->id,
            'is_active' => true,
        ]);
    }


    /** @test */
    public function test_admin_login_does_not_create_session(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $session = $this->service->startSession($user);

        $this->assertNull($session);

        $this->assertDatabaseMissing('cashier_histories', [
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function test_concurrent_login_closes_old_sessions(): void
    {
        $user = User::factory()->create(['role' => 'cashier']);

        session()->start();
        $sessionId = session()->getId();

        $oldCashierSession = CashierHistory::create([
            'user_id' => $user->id,
            'session_id' => $sessionId,
            'started_at' => now()->subHours(2),
            'last_activity_at' => now()->subHours(1),
            'is_active' => true,
        ]);

        $oldCashierSession = CashierHistory::create([
            'user_id' => $user->id,
            'session_id' => $sessionId,
            'started_at' => now()->subHours(3),
            'last_activity_at' => now()->subHours(2),
            'is_active' => true,
        ]);

        $newSession = $this->service->startSession($user);

        $this->assertDatabaseHas('cashier_histories', [
            'id' => $oldCashierSession->id,
            'is_active' => false,
        ]);
        $this->assertNotNull(CashierHistory::find($oldCashierSession->id)->ended_at);

        $this->assertDatabaseHas('cashier_histories', [
            'id' => $oldCashierSession->id,
            'is_active' => false,
        ]);
        $this->assertNotNull(CashierHistory::find($oldCashierSession->id)->ended_at);

        $this->assertTrue($newSession->is_active);
        $this->assertNull($newSession->ended_at);
    }

    /** @test */
    public function test_end_session_on_logout(): void
    {
        $user = User::factory()->create(['role' => 'cashier']);

        $session = CashierHistory::create([
            'user_id' => $user->id,
            'started_at' => now(),
            'last_activity_at' => now(),
            'is_active' => true,
        ]);

        $this->service->endSession($session);

        $session->refresh();

        $this->assertFalse($session->is_active);
        $this->assertNotNull($session->ended_at);
    }

    /** @test */
    public function test_close_expired_sessions(): void
    {
        $cashierUser = User::factory()->create(['role' => 'cashier']);
        CashierHistory::create([
            'user_id' => $cashierUser->id,
            'started_at' => now()->subHours(2),
            'last_activity_at' => now()->subMinutes(45),
            'is_active' => true,
        ]);

        $cashierUser = User::factory()->create(['role' => 'cashier']);
        CashierHistory::create([
            'user_id' => $cashierUser->id,
            'started_at' => now()->subHours(2),
            'last_activity_at' => now()->subMinutes(45),
            'is_active' => true,
        ]);

        $closedCount = $this->service->closeExpiredSessions(30);

        $this->assertEquals(2, $closedCount);

        $this->assertDatabaseMissing('cashier_histories', [
            'user_id' => $cashierUser->id,
            'is_active' => true,
        ]);
        $this->assertDatabaseMissing('cashier_histories', [
            'user_id' => $cashierUser->id,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function test_close_expired_sessions_only_when_idle(): void
    {
        $cashierUser = User::factory()->create(['role' => 'cashier']);
        CashierHistory::create([
            'user_id' => $cashierUser->id,
            'started_at' => now()->subHours(2),
            'last_activity_at' => now()->subMinutes(10),
            'is_active' => true,
        ]);

        $cashierUser = User::factory()->create(['role' => 'cashier']);
        CashierHistory::create([
            'user_id' => $cashierUser->id,
            'started_at' => now()->subHours(2),
            'last_activity_at' => now()->subMinutes(10),
            'is_active' => true,
        ]);

        $closedCount = $this->service->closeExpiredSessions(30);

        $this->assertEquals(0, $closedCount);

        $this->assertDatabaseHas('cashier_histories', [
            'user_id' => $cashierUser->id,
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('cashier_histories', [
            'user_id' => $cashierUser->id,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function test_get_active_session_returns_correct_session(): void
    {
        $user = User::factory()->create(['role' => 'cashier']);

        $session = CashierHistory::create([
            'user_id' => $user->id,
            'started_at' => now(),
            'last_activity_at' => now(),
            'is_active' => true,
        ]);

        $activeSession = $this->service->getActiveSession($user);

        $this->assertInstanceOf(CashierHistory::class, $activeSession);
        $this->assertEquals($session->id, $activeSession->id);
        $this->assertTrue($activeSession->is_active);
    }

    /** @test */
    public function test_get_order_count_for_cashier_session(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);

        $session = CashierHistory::create([
            'user_id' => $cashier->id,
            'started_at' => now()->subHours(2),
            'last_activity_at' => now()->subMinutes(30),
            'is_active' => true,
        ]);

        Order::factory()->count(3)->create([
            'cashier_id' => $cashier->id,
            'payment_method' => 'cash',
            'created_at' => now()->subHour(),
        ]);

        $count = $this->service->getOrderCount($session);

        $this->assertEquals(3, $count);
    }

    /** @test */
    public function test_order_count_excludes_orders_outside_session(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);

        $session = CashierHistory::create([
            'user_id' => $cashier->id,
            'started_at' => now()->subHours(2),
            'last_activity_at' => now(),
            'is_active' => true,
        ]);

        Order::factory()->create([
            'cashier_id' => $cashier->id,
            'payment_method' => 'cash',
            'created_at' => now()->subHours(5),
        ]);

        Order::factory()->create([
            'cashier_id' => $cashier->id,
            'payment_method' => 'cash',
            'created_at' => now()->subHour(),
        ]);

        $count = $this->service->getOrderCount($session);
        $this->assertEquals(1, $count);

        $cashier2 = User::factory()->create(['role' => 'cashier']);

        $endedSession = CashierHistory::create([
            'user_id' => $cashier2->id,
            'started_at' => now()->subHours(4),
            'ended_at' => now()->subHours(3),
            'last_activity_at' => now()->subHours(3),
            'is_active' => false,
        ]);

        Order::factory()->create([
            'cashier_id' => $cashier2->id,
            'payment_method' => 'cash',
            'created_at' => now()->subHours(5),
        ]);

        Order::factory()->create([
            'cashier_id' => $cashier2->id,
            'payment_method' => 'cash',
            'created_at' => now()->subHours(2),
        ]);

        Order::factory()->create([
            'cashier_id' => $cashier2->id,
            'payment_method' => 'cash',
            'created_at' => now()->subMinutes(210),
        ]);

        $endedCount = $this->service->getOrderCount($endedSession);
        $this->assertEquals(1, $endedCount);
    }

    /** @test */
    public function test_order_count_is_zero_when_no_orders(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);

        $session = CashierHistory::create([
            'user_id' => $cashier->id,
            'started_at' => now()->subHours(2),
            'last_activity_at' => now(),
            'is_active' => true,
        ]);

        $count = $this->service->getOrderCount($session);

        $this->assertEquals(0, $count);
    }

    /** @test */
    public function test_user_without_role_does_not_get_session(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $session = $this->service->getActiveSession($user);

        $this->assertNull($session);
    }

    /** @test */
    public function test_middleware_updates_last_activity(): void
    {
        Carbon::setTestNow(now());

        $user = User::factory()->create(['role' => 'cashier']);

        $session = CashierHistory::create([
            'user_id' => $user->id,
            'started_at' => now()->subMinutes(10),
            'last_activity_at' => now()->subMinutes(5),
            'is_active' => true,
        ]);

        $this->actingAs($user);

        $middleware = app(TrackCashierHistory::class);
        $request = Request::create('/test', 'GET');

        $middleware->handle($request, fn ($req) => response('ok'));

        $session->refresh();

        $this->assertEquals(
            now()->timestamp,
            $session->last_activity_at->timestamp,
            'last_activity_at should be updated to current time'
        );

        Carbon::setTestNow();
    }

    /** @test */
    public function test_middleware_closes_expired_session(): void
    {
        Carbon::setTestNow(now());

        $cashierUser = User::factory()->create(['role' => 'cashier']);

        $oldCashierSession = CashierHistory::create([
            'user_id' => $cashierUser->id,
            'started_at' => now()->subHours(2),
            'last_activity_at' => now()->subMinutes(45),
            'is_active' => true,
        ]);

        $cashierUser = User::factory()->create(['role' => 'cashier']);

        $oldCashierSession = CashierHistory::create([
            'user_id' => $cashierUser->id,
            'started_at' => now()->subHours(2),
            'last_activity_at' => now()->subMinutes(45),
            'is_active' => true,
        ]);

        $this->actingAs($cashierUser);

        $middleware = app(TrackCashierHistory::class);
        $request = Request::create('/test', 'GET');

        $middleware->handle($request, fn ($req) => response('ok'));

        $this->assertFalse(CashierHistory::find($oldCashierSession->id)->is_active);
        $this->assertNotNull(CashierHistory::find($oldCashierSession->id)->ended_at);

        $this->assertFalse(CashierHistory::find($oldCashierSession->id)->is_active);
        $this->assertNotNull(CashierHistory::find($oldCashierSession->id)->ended_at);

        Carbon::setTestNow();
    }

    /** @test */
    public function test_middleware_skips_non_staff_users(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user);

        $middleware = app(TrackCashierHistory::class);
        $request = Request::create('/test', 'GET');

        $middleware->handle($request, fn ($req) => response('ok'));

        $this->assertDatabaseMissing('cashier_histories', [
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function test_middleware_throttles_activity_update(): void
    {
        Carbon::setTestNow(now());

        $user = User::factory()->create(['role' => 'cashier']);

        $session = CashierHistory::create([
            'user_id' => $user->id,
            'started_at' => now()->subMinutes(10),
            'last_activity_at' => now()->subMinutes(2),
            'is_active' => true,
        ]);

        $this->actingAs($user);

        $middleware = app(TrackCashierHistory::class);
        $request = Request::create('/test', 'GET');

        $middleware->handle($request, fn ($req) => response('ok'));

        $session->refresh();
        $firstUpdateTime = $session->last_activity_at->timestamp;

        $middleware->handle($request, fn ($req) => response('ok'));

        $session->refresh();
        $secondUpdateTime = $session->last_activity_at->timestamp;

        $this->assertEquals(
            $firstUpdateTime,
            $secondUpdateTime,
            'second call within 60 seconds should not update last_activity_at again'
        );

        Carbon::setTestNow();
    }

    /** @test */
    public function test_login_creates_session_via_controller(): void
    {
        $user = User::factory()->create([
            'role' => 'cashier',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post(route('kasir.login.attempt'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('cashier_histories', [
            'user_id' => $user->id,
            'is_active' => true,
        ]);
    }

}
