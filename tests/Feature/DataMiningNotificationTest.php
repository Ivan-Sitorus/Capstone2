<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\DataMiningStatus;
use App\Models\DataminingRun;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DataMiningNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_poll_sends_notification_when_run_completes(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin, 'admin');

        $component = Livewire::test(DataMiningStatus::class);

        DataminingRun::create([
            'type'       => 'clustering',
            'status'     => 'completed',
            'parameters' => ['date_from' => '2025-01-01', 'date_to' => '2025-06-30'],
            'payload'    => ['best_k' => 3, 'silhouette_score' => 0.7],
        ]);

        $component->call('poll');

        $this->assertDatabaseHas('notifications', [
            'notifiable_id'   => $admin->id,
            'notifiable_type' => User::class,
        ]);
    }

    public function test_poll_sends_danger_notification_when_run_fails(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin, 'admin');

        $component = Livewire::test(DataMiningStatus::class);

        DataminingRun::create([
            'type'       => 'prediction',
            'status'     => 'failed',
            'parameters' => ['date_from' => '2025-01-01', 'date_to' => '2025-06-30'],
            'error'      => 'Data terlalu sedikit',
        ]);

        $component->call('poll');

        $notification = $admin->notifications()->first();

        $this->assertNotNull($notification);
        $this->assertStringContainsString('gagal', $notification->data['title']);
    }
}
