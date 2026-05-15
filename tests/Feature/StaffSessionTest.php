<?php

namespace Tests\Feature;

use App\Http\Middleware\TrackStaffSession;
use App\Models\CashierSession;
use App\Models\KitchenSession;
use App\Models\Order;
use App\Models\User;
use App\Services\StaffSessionService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class StaffSessionTest extends TestCase
{
    use RefreshDatabase;

    private StaffSessionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new StaffSessionService();
    }

    /** @test */
    public function test_start_cashier_session_creates_record(): void
    {
        $user = User::factory()->create(['role' => 'cashier']);

        $session = $this->service->startSession($user);

        $this->assertInstanceOf(CashierSession::class, $session);
        $this->assertNotNull($session->id);
        $this->assertEquals($user->id, $session->user_id);
        $this->assertTrue($session->is_active);
        $this->assertNotNull($session->started_at);
        $this->assertNotNull($session->last_activity_at);
        $this->assertNull($session->ended_at);

        $this->assertDatabaseHas('cashier_sessions', [
            'user_id' => $user->id,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function test_start_kitchen_session_creates_record(): void
    {
        $user = User::factory()->create(['role' => 'kitchen']);

        $session = $this->service->startSession($user);

        $this->assertInstanceOf(KitchenSession::class, $session);
        $this->assertNotNull($session->id);
        $this->assertEquals($user->id, $session->user_id);
        $this->assertTrue($session->is_active);
        $this->assertNotNull($session->started_at);
        $this->assertNotNull($session->last_activity_at);
        $this->assertNull($session->ended_at);

        $this->assertDatabaseHas('kitchen_sessions', [
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

        $this->assertDatabaseMissing('cashier_sessions', [
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseMissing('kitchen_sessions', [
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function test_concurrent_login_closes_old_sessions(): void
    {
        $user = User::factory()->create(['role' => 'cashier']);

        // Create an old active cashier session
        $oldCashierSession = CashierSession::create([
            'user_id' => $user->id,
            'started_at' => now()->subHours(2),
            'last_activity_at' => now()->subHours(1),
            'is_active' => true,
        ]);

        // Create an old active kitchen session (should also be closed)
        $oldKitchenSession = KitchenSession::create([
            'user_id' => $user->id,
            'started_at' => now()->subHours(3),
            'last_activity_at' => now()->subHours(2),
            'is_active' => true,
        ]);

        // Start new session
        $newSession = $this->service->startSession($user);

        // Old cashier session should be closed
        $this->assertDatabaseHas('cashier_sessions', [
            'id' => $oldCashierSession->id,
            'is_active' => false,
        ]);
        $this->assertNotNull(CashierSession::find($oldCashierSession->id)->ended_at);

        // Old kitchen session should be closed
        $this->assertDatabaseHas('kitchen_sessions', [
            'id' => $oldKitchenSession->id,
            'is_active' => false,
        ]);
        $this->assertNotNull(KitchenSession::find($oldKitchenSession->id)->ended_at);

        // New session should be active
        $this->assertTrue($newSession->is_active);
        $this->assertNull($newSession->ended_at);
    }

    /** @test */
    public function test_end_session_on_logout(): void
    {
        $user = User::factory()->create(['role' => 'cashier']);

        $session = CashierSession::create([
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
        CashierSession::create([
            'user_id' => $cashierUser->id,
            'started_at' => now()->subHours(2),
            'last_activity_at' => now()->subMinutes(45),
            'is_active' => true,
        ]);

        $kitchenUser = User::factory()->create(['role' => 'kitchen']);
        KitchenSession::create([
            'user_id' => $kitchenUser->id,
            'started_at' => now()->subHours(2),
            'last_activity_at' => now()->subMinutes(45),
            'is_active' => true,
        ]);

        $closedCount = $this->service->closeExpiredSessions(30);

        $this->assertEquals(2, $closedCount);

        $this->assertDatabaseMissing('cashier_sessions', [
            'user_id' => $cashierUser->id,
            'is_active' => true,
        ]);
        $this->assertDatabaseMissing('kitchen_sessions', [
            'user_id' => $kitchenUser->id,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function test_close_expired_sessions_only_when_idle(): void
    {
        $cashierUser = User::factory()->create(['role' => 'cashier']);
        CashierSession::create([
            'user_id' => $cashierUser->id,
            'started_at' => now()->subHours(2),
            'last_activity_at' => now()->subMinutes(10),
            'is_active' => true,
        ]);

        $kitchenUser = User::factory()->create(['role' => 'kitchen']);
        KitchenSession::create([
            'user_id' => $kitchenUser->id,
            'started_at' => now()->subHours(2),
            'last_activity_at' => now()->subMinutes(10),
            'is_active' => true,
        ]);

        $closedCount = $this->service->closeExpiredSessions(30);

        $this->assertEquals(0, $closedCount);

        $this->assertDatabaseHas('cashier_sessions', [
            'user_id' => $cashierUser->id,
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('kitchen_sessions', [
            'user_id' => $kitchenUser->id,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function test_get_active_session_returns_correct_session(): void
    {
        $user = User::factory()->create(['role' => 'cashier']);

        $session = CashierSession::create([
            'user_id' => $user->id,
            'started_at' => now(),
            'last_activity_at' => now(),
            'is_active' => true,
        ]);

        $activeSession = $this->service->getActiveSession($user);

        $this->assertInstanceOf(CashierSession::class, $activeSession);
        $this->assertEquals($session->id, $activeSession->id);
        $this->assertTrue($activeSession->is_active);
    }

    /** @test */
    public function test_get_order_count_for_cashier_session(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);

        $session = CashierSession::create([
            'user_id' => $cashier->id,
            'started_at' => now()->subHours(2),
            'last_activity_at' => now()->subMinutes(30),
            'is_active' => true,
        ]);

        // Create orders within the session range
        Order::factory()->count(3)->create([
            'cashier_id' => $cashier->id,
            'payment_method' => 'cash',
            'created_at' => now()->subHour(),
        ]);

        $count = $this->service->getOrderCount($session);

        $this->assertEquals(3, $count);
    }

    /** @test */
    public function test_get_order_count_for_kitchen_session(): void
    {
        $kitchen = User::factory()->create(['role' => 'kitchen']);

        $session = KitchenSession::create([
            'user_id' => $kitchen->id,
            'started_at' => now()->subHours(3),
            'last_activity_at' => now()->subMinutes(15),
            'is_active' => true,
        ]);

        // Create "selesai" orders within the session range
        Order::factory()->count(4)->create([
            'processed_by' => $kitchen->id,
            'status' => 'selesai',
            'payment_method' => 'cash',
            'created_at' => now()->subHours(2),
        ]);

        // Create a non-selesai order — should NOT be counted
        Order::factory()->create([
            'processed_by' => $kitchen->id,
            'status' => 'diproses',
            'created_at' => now()->subHours(2),
        ]);

        $count = $this->service->getOrderCount($session);

        $this->assertEquals(4, $count);
    }

    /** @test */
    public function test_order_count_excludes_orders_outside_session(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);

        $session = CashierSession::create([
            'user_id' => $cashier->id,
            'started_at' => now()->subHours(2),
            'last_activity_at' => now(),
            'is_active' => true,
        ]);

        // Order before session started — should NOT be counted
        Order::factory()->create([
            'cashier_id' => $cashier->id,
            'payment_method' => 'cash',
            'created_at' => now()->subHours(5),
        ]);

        // Order within session range — should be counted
        Order::factory()->create([
            'cashier_id' => $cashier->id,
            'payment_method' => 'cash',
            'created_at' => now()->subHour(),
        ]);

        $count = $this->service->getOrderCount($session);
        $this->assertEquals(1, $count);

        // Now test with an ended session (different cashier to avoid cross-contamination)
        $cashier2 = User::factory()->create(['role' => 'cashier']);

        $endedSession = CashierSession::create([
            'user_id' => $cashier2->id,
            'started_at' => now()->subHours(4),
            'ended_at' => now()->subHours(3),
            'last_activity_at' => now()->subHours(3),
            'is_active' => false,
        ]);

        // Before session start — exclude
        Order::factory()->create([
            'cashier_id' => $cashier2->id,
            'payment_method' => 'cash',
            'created_at' => now()->subHours(5),
        ]);

        // After session ended — exclude
        Order::factory()->create([
            'cashier_id' => $cashier2->id,
            'payment_method' => 'cash',
            'created_at' => now()->subHours(2),
        ]);

        // Within session range [4h ago, 3h ago] — include
        Order::factory()->create([
            'cashier_id' => $cashier2->id,
            'payment_method' => 'cash',
            'created_at' => now()->subMinutes(210), // 3.5h ago
        ]);

        $endedCount = $this->service->getOrderCount($endedSession);
        $this->assertEquals(1, $endedCount);
    }

    /** @test */
    public function test_order_count_is_zero_when_no_orders(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);

        $session = CashierSession::create([
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

        $session = CashierSession::create([
            'user_id' => $user->id,
            'started_at' => now()->subMinutes(10),
            'last_activity_at' => now()->subMinutes(5),
            'is_active' => true,
        ]);

        $this->actingAs($user);

        $middleware = new TrackStaffSession();
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

        $oldCashierSession = CashierSession::create([
            'user_id' => $cashierUser->id,
            'started_at' => now()->subHours(2),
            'last_activity_at' => now()->subMinutes(45),
            'is_active' => true,
        ]);

        $kitchenUser = User::factory()->create(['role' => 'kitchen']);

        $oldKitchenSession = KitchenSession::create([
            'user_id' => $kitchenUser->id,
            'started_at' => now()->subHours(2),
            'last_activity_at' => now()->subMinutes(45),
            'is_active' => true,
        ]);

        // Act as cashier to trigger middleware (closeExpiredSessions runs regardless of role)
        $this->actingAs($cashierUser);

        $middleware = new TrackStaffSession();
        $request = Request::create('/test', 'GET');

        $middleware->handle($request, fn ($req) => response('ok'));

        // Old expired sessions should be closed
        $this->assertFalse(CashierSession::find($oldCashierSession->id)->is_active);
        $this->assertNotNull(CashierSession::find($oldCashierSession->id)->ended_at);

        $this->assertFalse(KitchenSession::find($oldKitchenSession->id)->is_active);
        $this->assertNotNull(KitchenSession::find($oldKitchenSession->id)->ended_at);

        Carbon::setTestNow();
    }

    /** @test */
    public function test_middleware_skips_non_staff_users(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user);

        $middleware = new TrackStaffSession();
        $request = Request::create('/test', 'GET');

        $middleware->handle($request, fn ($req) => response('ok'));

        $this->assertDatabaseMissing('cashier_sessions', [
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseMissing('kitchen_sessions', [
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function test_middleware_throttles_activity_update(): void
    {
        Carbon::setTestNow(now());

        $user = User::factory()->create(['role' => 'cashier']);

        $session = CashierSession::create([
            'user_id' => $user->id,
            'started_at' => now()->subMinutes(10),
            'last_activity_at' => now()->subMinutes(2),
            'is_active' => true,
        ]);

        $this->actingAs($user);

        $middleware = new TrackStaffSession();
        $request = Request::create('/test', 'GET');

        // First call: last_activity_at is 120 seconds old → should update
        $middleware->handle($request, fn ($req) => response('ok'));

        $session->refresh();
        $firstUpdateTime = $session->last_activity_at->timestamp;

        // Second call: last_activity_at was just updated → < 60 seconds → should skip
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

        $this->assertDatabaseHas('cashier_sessions', [
            'user_id' => $user->id,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function test_logout_ends_session_via_controller(): void
    {
        $user = User::factory()->create([
            'role' => 'cashier',
            'password' => bcrypt('password'),
        ]);

        // Login to create a session
        $this->post(route('kasir.login.attempt'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $session = CashierSession::where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        $this->assertNotNull($session, 'Session should exist after login');
        $this->assertNull($session->ended_at);

        // Logout
        $response = $this->post(route('logout'));

        $session->refresh();

        $this->assertFalse($session->is_active);
        $this->assertNotNull($session->ended_at);
    }
}
