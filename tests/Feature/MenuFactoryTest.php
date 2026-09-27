<?php

namespace Tests\Feature;

use App\Enums\MenuStatus;
use App\Models\Menu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuFactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_menu_factory_persists_menu_with_expected_name_and_status(): void
    {
        $name = 'Kopi Uji Coba';

        $menu = Menu::factory()->create(['name' => $name]);

        $this->assertInstanceOf(Menu::class, $menu);
        $this->assertNotNull($menu->id);
        $this->assertNotNull($menu->category_id);

        $this->assertDatabaseHas('menus', [
            'id' => $menu->id,
            'name' => $name,
            'status' => MenuStatus::Active->value,
        ]);

        $fresh = $menu->fresh();
        $this->assertSame($name, $fresh->name);
        $this->assertSame(MenuStatus::Active, $fresh->status);
    }
}
