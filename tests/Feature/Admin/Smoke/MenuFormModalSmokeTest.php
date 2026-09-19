<?php

namespace Tests\Feature\Admin\Smoke;

use App\Filament\Resources\MenuResource\Pages\ListMenus;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MenuFormModalSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_menu_create_modal_renders(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin, 'admin');

        Filament::setCurrentPanel('admin');

        Livewire::test(ListMenus::class)
            ->mountAction('create')
            ->assertHasNoErrors();
    }
}
