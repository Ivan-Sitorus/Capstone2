<?php

namespace Tests\Feature\Inventory;

use App\Models\Ingredient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockAdminSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_adjustment_and_history_pages_render(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $ingredient = Ingredient::factory()->create(['batch_mode' => 'fefo']);

        $this->actingAs($admin, 'admin');

        $this->get('/admin/penyesuaian-stok')->assertSuccessful();
        $this->get('/admin/bahan-baku')->assertSuccessful();
        $this->get('/admin/bahan-baku/' . $ingredient->id . '/history')->assertSuccessful();
    }
}
