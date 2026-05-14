<?php

namespace Tests\Feature\Auth;

use App\Models\ActiveStaffSession;
use App\Models\DeviceSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceAuthTest extends TestCase
{
    use RefreshDatabase;

    private function createDeviceSession(): DeviceSession
    {
        $deviceSession = DeviceSession::create([
            'device_uuid' => '550e8400-e29b-41d4-a716-446655440000',
            'device_name' => 'TestBrowser/1.0',
            'last_seen_at' => now(),
        ]);

        // Simulate what InitializeDeviceSession middleware does
        session()->put('device_session_id', $deviceSession->device_uuid);

        return $deviceSession;
    }

    public function test_login_with_valid_credentials_returns_staff_session(): void
    {
        $this->withoutExceptionHandling();

        User::factory()->create([
            'email' => 'kasir@test.com',
            'password' => 'password',
            'role' => 'cashier',
        ]);

        $this->createDeviceSession();

        $response = $this->postJson('/api/device/login', [
            'email' => 'kasir@test.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'staff_session_id',
                'user' => ['id', 'name', 'email', 'role'],
            ])
            ->assertJsonPath('user.email', 'kasir@test.com')
            ->assertJsonPath('user.role', 'cashier');
    }

    public function test_login_with_invalid_credentials_returns_401(): void
    {
        User::factory()->create([
            'email' => 'kasir@test.com',
            'password' => 'password',
            'role' => 'cashier',
        ]);

        $this->createDeviceSession();

        $response = $this->postJson('/api/device/login', [
            'email' => 'kasir@test.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJsonFragment([
                'message' => 'Email atau kata sandi salah.',
            ]);
    }

    public function test_active_staff_list_returns_correct_count(): void
    {
        $this->createDeviceSession();
        $deviceSession = DeviceSession::first();

        $user1 = User::factory()->create(['role' => 'cashier']);
        $user2 = User::factory()->create(['role' => 'cashier']);

        ActiveStaffSession::create([
            'device_session_id' => $deviceSession->id,
            'user_id' => $user1->id,
            'pin_verified_at' => now(),
            'active_context' => 'active',
        ]);

        ActiveStaffSession::create([
            'device_session_id' => $deviceSession->id,
            'user_id' => $user2->id,
            'pin_verified_at' => now(),
            'active_context' => null,
        ]);

        $response = $this->getJson('/api/device/active-staff');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'sessions');
    }

    public function test_logout_removes_session(): void
    {
        $this->createDeviceSession();
        $deviceSession = DeviceSession::first();

        $user = User::factory()->create(['role' => 'cashier']);

        $staffSession = ActiveStaffSession::create([
            'device_session_id' => $deviceSession->id,
            'user_id' => $user->id,
            'pin_verified_at' => now(),
            'active_context' => 'active',
        ]);

        $this->assertDatabaseHas('active_staff_sessions', [
            'id' => $staffSession->id,
        ]);

        $response = $this->postJson('/api/device/logout', [
            'staff_session_id' => $staffSession->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Staff session deleted.']);

        $this->assertDatabaseMissing('active_staff_sessions', [
            'id' => $staffSession->id,
        ]);
    }

    public function test_logout_all_removes_all_sessions_for_device(): void
    {
        $this->createDeviceSession();
        $deviceSession = DeviceSession::first();

        $user1 = User::factory()->create(['role' => 'cashier']);
        $user2 = User::factory()->create(['role' => 'cashier']);

        $session1 = ActiveStaffSession::create([
            'device_session_id' => $deviceSession->id,
            'user_id' => $user1->id,
            'pin_verified_at' => now(),
            'active_context' => 'active',
        ]);

        $session2 = ActiveStaffSession::create([
            'device_session_id' => $deviceSession->id,
            'user_id' => $user2->id,
            'pin_verified_at' => now(),
            'active_context' => null,
        ]);

        $this->assertDatabaseHas('active_staff_sessions', ['id' => $session1->id]);
        $this->assertDatabaseHas('active_staff_sessions', ['id' => $session2->id]);

        $response = $this->postJson('/api/device/logout-all');

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'All staff sessions on this device deleted.']);

        $this->assertDatabaseMissing('active_staff_sessions', ['id' => $session1->id]);
        $this->assertDatabaseMissing('active_staff_sessions', ['id' => $session2->id]);
    }

    public function test_customer_cannot_login_via_device_auth(): void
    {
        User::factory()->create([
            'email' => 'customer@test.com',
            'password' => 'password',
            'role' => 'customer',
        ]);

        $this->createDeviceSession();

        $response = $this->postJson('/api/device/login', [
            'email' => 'customer@test.com',
            'password' => 'password',
        ]);

        $response->assertStatus(401)
            ->assertJsonFragment([
                'message' => 'Email atau kata sandi salah.',
            ]);
    }
}
