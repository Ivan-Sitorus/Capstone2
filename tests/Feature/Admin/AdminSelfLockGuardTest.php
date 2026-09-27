<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminSelfLockGuardTest extends TestCase
{
    use RefreshDatabase;

    private function admin(array $attributes = []): User
    {
        return User::factory()->create([
            'role' => UserRole::Admin->value,
            'status' => UserStatus::Active->value,
            ...$attributes,
        ]);
    }

    private function asAdmin(User $admin): void
    {
        $this->actingAs($admin, 'admin');
        Filament::setCurrentPanel('admin');
    }

    private function editAction(User $record): TestAction
    {
        return TestAction::make(EditAction::class)->table($record);
    }

    private function deleteAction(User $record): TestAction
    {
        return TestAction::make(DeleteAction::class)->table($record);
    }

    public function test_admin_cannot_deactivate_self(): void
    {
        $admin = $this->admin();
        $this->admin();
        $this->asAdmin($admin);

        Livewire::test(ListUsers::class)
            ->callAction($this->editAction($admin), data: [
                'role' => UserRole::Admin->value,
                'status' => UserStatus::Inactive->value,
            ])
            ->assertActionHalted($this->editAction($admin))
            ->assertNotified('Tidak Dapat Menonaktifkan Akun Sendiri');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'status' => UserStatus::Active->value,
        ]);
    }

    public function test_admin_cannot_delete_self(): void
    {
        $admin = $this->admin();
        $this->admin();
        $this->asAdmin($admin);

        Livewire::test(ListUsers::class)
            ->callAction($this->deleteAction($admin))
            ->assertActionHalted($this->deleteAction($admin))
            ->assertNotified('Tidak Dapat Menghapus Akun Sendiri');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_last_active_admin_cannot_be_deactivated(): void
    {
        $admin = $this->admin();
        $this->asAdmin($admin);

        Livewire::test(ListUsers::class)
            ->callAction($this->editAction($admin), data: [
                'role' => UserRole::Admin->value,
                'status' => UserStatus::Inactive->value,
            ])
            ->assertActionHalted($this->editAction($admin))
            ->assertNotified('Tidak Dapat Menonaktifkan Admin Terakhir');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'status' => UserStatus::Active->value,
        ]);
    }

    public function test_last_active_admin_cannot_be_demoted(): void
    {
        $admin = $this->admin();
        $this->asAdmin($admin);

        Livewire::test(ListUsers::class)
            ->callAction($this->editAction($admin), data: [
                'role' => UserRole::Cashier->value,
                'status' => UserStatus::Active->value,
            ])
            ->assertActionHalted($this->editAction($admin))
            ->assertNotified('Tidak Dapat Menurunkan Admin Terakhir');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'role' => UserRole::Admin->value,
        ]);
    }

    public function test_non_last_active_admin_can_be_deactivated(): void
    {
        $admin = $this->admin();
        $target = $this->admin();
        $this->asAdmin($admin);

        Livewire::test(ListUsers::class)
            ->callAction($this->editAction($target), data: [
                'role' => UserRole::Admin->value,
                'status' => UserStatus::Inactive->value,
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'status' => UserStatus::Inactive->value,
        ]);

        $this->assertSame(1, User::query()
            ->where('role', UserRole::Admin->value)
            ->where('status', UserStatus::Active->value)
            ->count());
    }
}
