<?php

namespace Tests\Feature;

use App\Http\Middleware\TrackStaffSession;
use App\Models\Order;
use App\Models\StaffSession;
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

        $this->assertInstanceOf(StaffSession::class, $session);
        $this->assertEquals('cashier', $session->type);
        $this->assertNotNull($session->id);
        $this->assertEquals($user->id, $session->user_id);
        $this->assertTrue($session->is_active);
        $this->assertNotNull($session->started_at);
        $this->assertNotNull($session->last_activity_at);
        $this->assertNull($session->ended_at);

        $this->assertDatabaseHas('staff_sessions', [
            'user_id' => $user->id,
            'type' => 'cashier',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function test_start_kitchen_session_creates_record(): void
    {
        $user = User::factory()->create(['role' => 'kitchen']);

        $session = $this->service->startSession($user);

        $this->assertInstanceOf(StaffSession::class, $session);
        $this->assertEquals('kitchen', $session->type);
        $this->assertNotNull($session->id);
        $this->assertEquals($user->id, $session->user_id);
        $this->assertTrue($session->is_active);
        $this->assertNotNull($session->started_at);
        $this->assertNotNull($session->last_activity_at);
        $this->assertNull($session->ended_at);

        $this->assertDatabaseHas('staff_sessions', [
            'user_id' => $user->id,
            'type' => 'kitchen',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function test_admin_login_does_not_create_session(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $session = $this->service->startSession($user);

        $this->assertNull($session);

        $this->assertDatabaseMissing('staff_sessions', [
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function test_concurrent_login_closes_old_sessions(): void
    {
        $user = User::factory()->create(['role' => 'cashier']);

        session()->start();
        $sessionId = session()->getId();

        $oldCashierSession = StaffSession::create([
            'user_id' => $user->id,
            'type' => 'cashier',
            'session_id' => $sessionId,
            'started_at' => now()->subHours(2),
            'last_activity_at' => now()->subHours(1),
            'is_active' => true,
        ]);

        $oldKitchenSession = StaffSession::create([
            'user_id' => $user->id,
            'type' => 'kitchen',
            'session_id' => $sessionId,
            'started_at' => now()->subHours(3),
            'last_activity_at' => now()->subHours(2),
            'is_active' => true,
        ]);

        $newSession = $this->service->startSession($user);

        $this->assertDatabaseHas('staff_sessions', [
            'id' => $oldCashierSession->id,
            'is_active' => false,
        ]);
        $this->assertNotNull(StaffSession::find($oldCashierSession->id)->ended_at);

        $this->assertDatabaseHas('staff_sessions', [
            'id' => $oldKitchenSession->id,
            'is_active' => false,
        ]);
        $this->assertNotNull(StaffSession::find($oldKitchenSession->id)->ended_at);

        $this->assertTrue($newSession->is_active);
        $this->assertNull($newSession->ended_at);
    }

    /** @test */
    public function test_end_session_on_logout(): void
    {
        $user = User::factory()->create(['role' => 'cashier']);

        $session = StaffSession::create([
            'user_id' => $user->id,
            'type' => 'cashier',
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
        StaffSession::create([
            'user_id' => $cashierUser->id,
            'type' => 'cashier',
            'started_at' => now()->subHours(2),
            'last_activity_at' => now()->subMinutes(45),
            'is_active' => true,
        ]);

        $kitchenUser = User::factory()->create(['role' => 'kitchen']);
        StaffSession::create([
            'user_id' => $kitchenUser->id,
            'type' => 'kitchen',
            'started_at' => now()->subHours(2),
            'last_activity_at' => now()->subMinutes(45),
            'is_active' => true,
        ]);

        $closedCount = $this->service->closeExpiredSessions(30);

        $this->assertEquals(2, $closedCount);

        $this->assertDatabaseMissing('staff_sessions', [
            'user_id' => $cashierUser->id,
            'is_active' => true,
        ]);
        $this->assertDatabaseMissing('staff_sessions', [
            'user_id' => $kitchenUser->id,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function test_close_expired_sessions_only_when_idle(): void
    {
        $cashierUser = User::factory()->create(['role' => 'cashier']);
        StaffSession::create([
            'user_id' => $cashierUser->id,
            'type' => 'cashier',
            'started_at' => now()->subHours(2),
            'last_activity_at' => now()->subMinutes(10),
            'is_active' => true,
        ]);

        $kitchenUser = User::factory()->create(['role' => 'kitchen']);
        StaffSession::create([
            'user_id' => $kitchenUser->id,
            'type' => 'kitchen',
            'started_at' => now()->subHours(2),
            'last_activity_at' => now()->subMinutes(10),
            'is_active' => true,
        ]);

        $closedCount = $this->service->closeExpiredSessions(30);

        $this->assertEquals(0, $closedCount);

        $this->assertDatabaseHas('staff_sessions', [
            'user_id' => $cashierUser->id,
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('staff_sessions', [
            'user_id' => $kitchenUser->id,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function test_get_active_session_returns_correct_session(): void
    {
        $user = User::factory()->create(['role' => 'cashier']);

        $session = StaffSession::create([
            'user_id' => $user->id,
            'type' => 'cashier',
            'started_at' => now(),
            'last_activity_at' => now(),
            'is_active' => true,
        ]);

        $activeSession = $this->service->getActiveSession($user);

        $this->assertInstanceOf(StaffSession::class, $activeSession);
        $this->assertEquals($session->id, $activeSession->id);
        $this->assertTrue($activeSession->is_active);
    }

    /** @test */
    public function test_get_order_count_for_cashier_session(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);

        $session = StaffSession::create([
            'user_id' => $cashier->id,
            'type' => 'cashier',
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
    public function test_get_order_count_for_kitchen_session(): void
    {
        $kitchen = User::factory()->create(['role' => 'kitchen']);

        $session = StaffSession::create([
            'user_id' => $kitchen->id,
            'type' => 'kitchen',
            'started_at' => now()->subHours(3),
            'last_activity_at' => now()->subMinutes(15),
            'is_active' => true,
        ]);

        Order::factory()->count(4)->create([
            'processed_by' => $kitchen->id,
            'status' => 'selesai',
            'payment_method' => 'cash',
            'created_at' => now()->subHours(2),
        ]);

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

        $session = StaffSession::create([
            'user_id' => $cashier->id,
            'type' => 'cashier',
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

        $endedSession = StaffSession::create([
            'user_id' => $cashier2->id,
            'type' => 'cashier',
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

        $session = StaffSession::create([
            'user_id' => $cashier->id,
            'type' => 'cashier',
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

        $session = StaffSession::create([
            'user_id' => $user->id,
            'type' => 'cashier',
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

        $oldCashierSession = StaffSession::create([
            'user_id' => $cashierUser->id,
            'type' => 'cashier',
            'started_at' => now()->subHours(2),
            'last_activity_at' => now()->subMinutes(45),
            'is_active' => true,
        ]);

        $kitchenUser = User::factory()->create(['role' => 'kitchen']);

        $oldKitchenSession = StaffSession::create([
            'user_id' => $kitchenUser->id,
            'type' => 'kitchen',
            'started_at' => now()->subHours(2),
            'last_activity_at' => now()->subMinutes(45),
            'is_active' => true,
        ]);

        $this->actingAs($cashierUser);

        $middleware = new TrackStaffSession();
        $request = Request::create('/test', 'GET');

        $middleware->handle($request, fn ($req) => response('ok'));

        $this->assertFalse(StaffSession::find($oldCashierSession->id)->is_active);
        $this->assertNotNull(StaffSession::find($oldCashierSession->id)->ended_at);

        $this->assertFalse(StaffSession::find($oldKitchenSession->id)->is_active);
        $this->assertNotNull(StaffSession::find($oldKitchenSession->id)->ended_at);

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

        $this->assertDatabaseMissing('staff_sessions', [
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function test_middleware_throttles_activity_update(): void
    {
        Carbon::setTestNow(now());

        $user = User::factory()->create(['role' => 'cashier']);

        $session = StaffSession::create([
            'user_id' => $user->id,
            'type' => 'cashier',
            'started_at' => now()->subMinutes(10),
            'last_activity_at' => now()->subMinutes(2),
            'is_active' => true,
        ]);

        $this->actingAs($user);

        $middleware = new TrackStaffSession();
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

        $this->assertDatabaseHas('staff_sessions', [
            'user_id' => $user->id,
            'type' => 'cashier',
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

        $this->post(route('kasir.login.attempt'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $session = StaffSession::where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        $this->assertNotNull($session, 'Session should exist after login');
        $this->assertNull($session->ended_at);

        $response = $this->post(route('logout'));

        $session->refresh();

        $this->assertFalse($session->is_active);
        $this->assertNotNull($session->ended_at);
    }
}
